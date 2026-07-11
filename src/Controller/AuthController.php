<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User\ChangePasswordType;
use App\Form\User\ForgotPasswordRequestType;
use App\Form\User\SignInType;
use App\Service\Security\TemporaryPasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthController extends AbstractController
{

    public function __construct(
        private readonly AuthenticationUtils         $authenticationUtils,
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Security                    $security,
        private readonly TranslatorInterface         $translator
    )
    {
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }


        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        TemporaryPasswordGenerator $temporaryPasswordGenerator,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Response {
        $form = $this->createForm(ForgotPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = strtolower(trim((string) $form->get('email')->getData()));
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $previousPassword = $user->getPassword();
                $previousMustChangePassword = $user->mustChangePassword();
                $temporaryPassword = $temporaryPasswordGenerator->generate();

                $message = $this->createTemporaryPasswordEmail($user, $temporaryPassword);
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $temporaryPassword));
                $user->setMustChangePassword(true);

                try {
                    $this->em->flush();
                    $mailer->send($message);
                } catch (\Throwable $exception) {
                    if ($previousPassword !== null) {
                        $user->setPassword($previousPassword);
                    }
                    $user->setMustChangePassword($previousMustChangePassword);
                    $this->em->flush();
                    $logger->error('Temporary password email could not be sent.', [
                        'exception' => $exception,
                        'userId' => $user->getId(),
                    ]);
                }
            }

            $this->addFlash('success', $this->translator->trans('forgot_password.request_success'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/temporary-password/change', name: 'app_temporary_password_change', methods: ['GET', 'POST'])]
    public function changeTemporaryPassword(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->mustChangePassword()) {
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = (string) $form->get('oldPassword')->getData();
            $newPassword = (string) $form->get('password')->getData();

            if (!$this->userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $form->get('oldPassword')->addError(new FormError($this->translator->trans('temporary_password.error.current_password_invalid')));

                return $this->renderTemporaryPasswordForm($form);
            }

            if ($oldPassword === $newPassword) {
                $form->get('password')->addError(new FormError($this->translator->trans('temporary_password.error.new_password_same_as_old')));

                return $this->renderTemporaryPasswordForm($form);
            }

            $user->setPassword($this->userPasswordHasher->hashPassword($user, $newPassword));
            $user->setMustChangePassword(false);
            $this->em->flush();
            $this->addFlash('success', $this->translator->trans('temporary_password.flash.updated'));

            return $this->redirectToRoute('app_home');
        }

        return $this->renderTemporaryPasswordForm($form);
    }

    #[Route('/sign-up', name: 'app_signin')]
    public function signin(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(SignInType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $alreadyExistUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($alreadyExistUser) {
                $this->addFlash('danger', $this->translator->trans('signup.error.email_already_used'));
                return $this->redirectToRoute('app_login');
            }

            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user = $form->getData();
            $user->setRoles(['ROLE_USER']);
            $this->em->persist($user);
            $this->em->flush();

            $this->security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/signin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function createTemporaryPasswordEmail(User $user, string $temporaryPassword): Email
    {
        $firstname = $user->getFirstname() ?: '';

        return (new Email())
            ->from('mulhousegaminglan@gmail.com')
            ->to((string) $user->getEmail())
            ->subject($this->translator->trans('forgot_password.email.subject'))
            ->text(implode("\n\n", [
                $this->translator->trans('forgot_password.email.greeting', ['%firstname%' => $firstname]),
                $this->translator->trans('forgot_password.email.line_1'),
                $this->translator->trans('forgot_password.email.line_2', ['%temporaryPassword%' => $temporaryPassword]),
                $this->translator->trans('forgot_password.email.line_3'),
                $this->translator->trans('forgot_password.email.line_4'),
            ]))
            ->html($this->renderView('emails/forgot_password.html.twig', [
                'user' => $user,
                'temporaryPassword' => $temporaryPassword,
            ]));
    }

    private function renderTemporaryPasswordForm(FormInterface $form): Response
    {
        return $this->render('user/change_temporary_password.html.twig', [
            'form' => $form->createView(),
            'passwordStrength' => $_ENV['PASSWORD_STRENGTH_VALUE'],
        ]);
    }
}

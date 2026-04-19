<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\User\SignInType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class AuthController extends AbstractController
{

    public function __construct(
        private readonly AuthenticationUtils         $authenticationUtils,
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserAuthenticatorInterface  $userAuthenticator,
        private readonly AppAuthenticator            $authenticator
    )
    {
    }

    #[Route('/connexion', name: 'app_login')]
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

    #[Route('/inscription', name: 'app_signin')]
    public function signin(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(SignInType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $alreadyExistUser = $this->em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($alreadyExistUser) {
                $this->addFlash('danger', 'Un compte existe déjà avec cette adresse email, essayez de vous connecter !');
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

            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );
        }

        return $this->render('user/signin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
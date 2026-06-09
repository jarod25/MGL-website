<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\User\AccountType;
use App\Form\User\ChangePasswordType;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use App\Service\AvailablePlacesService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
        private readonly EventRepository        $eventRepository,
        private readonly ParticipantRepository  $participantRepository,
        private readonly PaginatorInterface     $paginator,
    )
    {
    }

    #[Route('/profil', name: 'app_profile')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $participant = $this->participantRepository->findOneByUser($user);
        $email = $user->getEmail();

        $form_infos = $this->createForm(AccountType::class, $user, [
            'discord_pseudo' => $participant?->getDiscordPseudo(),
            'has_lan_participant' => $participant !== null,
        ]);
        $form_infos->handleRequest($request);

        $form_pwd = $this->createForm(ChangePasswordType::class);
        $form_pwd->handleRequest($request);

        if ($form_infos->isSubmitted() && $form_infos->isValid()) {
            if ($form_infos->get('email')->getData() !== $email) {
                $userExist = $this->userRepository->findOneBy(['email' => $form_infos->get('email')->getData()]);
                if ($userExist) {
                    $form_infos->get('email')->addError(new FormError($translator->trans('account.error.email_already_used')));

                    return $this->renderProfile($form_infos, $form_pwd, $participant !== null);
                }
            }

            if ($participant !== null && $form_infos->has('discordPseudo')) {
                $discordPseudo = trim((string) $form_infos->get('discordPseudo')->getData());
                $participant->setDiscordPseudo($discordPseudo !== '' ? $discordPseudo : null);
                $this->entityManager->persist($participant);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('account.flash.info_updated'));

            return $this->redirectToRoute('app_profile');
        }

        if ($form_pwd->isSubmitted() && $form_pwd->isValid()) {
            $oldPassword = $form_pwd->get('oldPassword')->getData();
            if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                $form_pwd->get('oldPassword')->addError(new FormError($translator->trans('account.error.current_password_invalid')));

                return $this->renderProfile($form_infos, $form_pwd, $participant !== null);
            }

            if ($form_pwd->get('oldPassword')->getData() === $form_pwd->get('password')->getData()) {
                $form_pwd->get('password')->addError(new FormError($translator->trans('account.error.new_password_same_as_old')));

                return $this->renderProfile($form_infos, $form_pwd, $participant !== null);
            }

            if ($form_pwd->get('password')->getData()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form_pwd->get('password')->getData()
                    )
                );
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', $translator->trans('account.flash.password_updated'));

            return $this->redirectToRoute('app_profile');
        }

        return $this->renderProfile($form_infos, $form_pwd, $participant !== null);
    }

    private function renderProfile(FormInterface $formInfos, FormInterface $formPwd, bool $hasLanParticipant): Response
    {
        return $this->render('user/profile.html.twig', [
            'form_infos' => $formInfos->createView(),
            'form_pwd' => $formPwd->createView(),
            'hasLanParticipant' => $hasLanParticipant,
        ]);
    }

    #[Route('/mes-evenements', name: 'app_event_my_events', methods: ['GET', 'POST'])]
    public function myEvents(Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', $translator->trans('account.error.login_required_events'));

            return $this->redirectToRoute('app_login');
        }

        $events = $this->eventRepository->findEventsByUser($user);
        $pagination = $this->paginator->paginate(
            $events,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('event/my_events.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/participants/{id}', name: 'app_participants', methods: ['GET'])]
    public function myParticipants(Request $request, Event $event): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($event->getOwner() !== $user) {
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $userEvents = $event->getParticipants();

        $users = [];
        foreach ($userEvents as $userEvent) {
            $users[] = $userEvent->getUser();
        }


        $pagination = $this->paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            6
        );
        return $this->render('user/my_participants.html.twig', [
            'pagination' => $pagination,
        ]);
    }

}

<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\User\AccountType;
use App\Form\User\ChangePasswordType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\AvailablePlacesService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
        private readonly EventRepository        $eventRepository,
        private readonly PaginatorInterface     $paginator,
    )
    {
    }

    #[Route('/profil', name: 'app_profile')]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        {
            $user = $this->getUser();

            if (!$user) {
                return $this->redirectToRoute('app_login');
            }

            $email = $user->getEmail();

            $form_infos = $this->createForm(AccountType::class, $user);
            $form_infos->handleRequest($request);

            $form_pwd = $this->createForm(ChangePasswordType::class);
            $form_pwd->handleRequest($request);

            if ($form_infos->isSubmitted() && $form_infos->isValid()) {
                if ($form_infos->get('email')->getData() !== $email) {
                    $userExist = $this->userRepository->findOneBy(['email' => $form_infos->get('email')->getData()]);
                    if ($userExist) {
                        $form_infos->get('email')->addError(new FormError('Cet email est déjà utilisé sur un autre compte.'));
                        return $this->render('user/profile.html.twig', [
                            'form_infos' => $form_infos->createView(),
                            'form_pwd' => $form_pwd->createView(),
                        ]);
                    }
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', 'Vos informations ont bien été mises à jour.');
                return $this->redirectToRoute('app_profile');
            }

            if ($form_pwd->isSubmitted() && $form_pwd->isValid()) {
                $oldPassword = $form_pwd->get('oldPassword')->getData();
                if (!$userPasswordHasher->isPasswordValid($user, $oldPassword)) {
                    $form_pwd->get('oldPassword')->addError(new FormError('Le mot de passe actuel est incorrect.'));
                    return $this->render('user/profile.html.twig', [
                        'form_infos' => $form_infos->createView(),
                        'form_pwd' => $form_pwd->createView(),
                    ]);
                }

                if ($form_pwd->get('oldPassword')->getData() === $form_pwd->get('password')->getData()) {
                    $form_pwd->get('password')->addError(new FormError('Le nouveau mot de passe doit être différent de l\'ancien.'));
                    return $this->render('user/profile.html.twig', [
                        'form_infos' => $form_infos->createView(),
                        'form_pwd' => $form_pwd->createView(),
                    ]);
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
                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
                return $this->redirectToRoute('app_profile');
            }
            return $this->render('user/profile.html.twig', [
                'form_infos' => $form_infos->createView(),
                'form_pwd' => $form_pwd->createView(),
            ]);
        }
    }

    #[Route('/mes-evenements', name: 'app_event_my_events', methods: ['GET', 'POST'])]
    public function myEvents(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour voir vos événements');

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

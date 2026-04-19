<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipants;
use App\Entity\User;
use App\Enum\PaymentStatusEnum;
use App\Event\Mail\ParticipantRegistrationEvent;
use App\Event\Mail\ParticipantUnregistrationEvent;
use App\Form\Event\EventFilterType;
use App\Form\Event\EventType;
use App\Model\EventSearch;
use App\Repository\EventParticipantsRepository;
use App\Repository\EventRepository;
use App\Security\Voter\EventVoter;
use App\Service\AvailablePlacesService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evenements')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly EventRepository             $eventRepository,
        private readonly PaginatorInterface          $paginator,
        private readonly MailService                 $mailService,
        private readonly AvailablePlacesService      $availablePlacesService,
        private readonly EventParticipantsRepository $eventParticipantsRepository
    )
    {
    }

    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $user = $this->getUser();

        $search = new EventSearch();
        $form = $this->createForm(EventFilterType::class, $search, ['user' => $user]);
        $form->handleRequest($request);

        if ($user)
            $query = $this->eventRepository->findBySearchCriteria($search, $user);
        else
            $query = $this->eventRepository->findBySearchCriteria($search);

        foreach ($query as $event) {
            $event->availablePlaces = $this->availablePlacesService->calculateAvailablePlaces($event);
        }

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('event/list.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'user' => $user ?? null,
        ]);
    }

    #[Route('/creer-un-evenement', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('danger', 'Vous devez être connecté pour créer un évènement');

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(EventType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $form->getData();
            $event->setOwner($this->getUser());

            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Évènement créé avec succès');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $user = $this->getUser();

        if (!$this->isGranted(EventVoter::VIEW, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de voir cet évènement.");

            return $this->redirectToRoute('app_event_index');
        }

        $isUserRegistered = (bool)$this->eventParticipantsRepository->findOneBy(['user' => $user, 'event' => $event]);

        $event->availablePlaces = $this->availablePlacesService->calculateAvailablePlaces($event);

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'isUserRegistered' => $isUserRegistered
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event): Response
    {
        if (!$this->isGranted(EventVoter::EDIT, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de modifier cet évènement.");

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data->isIsPayable() === false) {
                $data->setPrice(null);
            }

            $this->em->persist($event);
            $this->em->flush();

            $this->addFlash('success', 'Évènement mis à jour avec succès');

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event): Response
    {
        if (!$this->isGranted(EventVoter::DELETE, $event)) {
            $this->addFlash('danger', "Vous n'avez pas la permission de supprimer cet évènement.");

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $this->em->remove($event);
            $this->em->flush();
            $this->addFlash('success', 'Évènement supprimé avec succès');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/register', name: 'app_event_register', methods: ['GET', 'POST'])]
    public function register(Request $request, Event $event): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous inscrire');

            return $this->redirectToRoute('app_login');
        }

        $isUserRegistered = (bool)$this->eventParticipantsRepository->findOneBy(['user' => $user, 'event' => $event]);

        if ($isUserRegistered) {
            $this->addFlash('danger', 'Vous êtes déjà inscrit à cet événement.');

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($this->availablePlacesService->calculateAvailablePlaces($event) <= 0) {
            $this->addFlash('danger', 'Le nombre maximal de participants est atteint.');

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $participant = $this->eventParticipantsRepository->findOneBy(['user' => $user, 'event' => $event]);

        if ($event->isIsPayable() && $event->getPrice() > 0 && (!$participant || !$participant->isHasPaid())) {
            return $this->redirectToRoute('app_pay_event', ['id' => $event->getId()]);
        }

        $eventParticipant = new EventParticipants();
        $eventParticipant->setEvent($event);
        $eventParticipant->setUser($user);
        $this->em->persist($eventParticipant);
        $this->em->flush();

        // Envoyer un email de confirmation
        $email = new ParticipantRegistrationEvent($user->getEmail());
        $email->setParams([
            'user' => $user,
            'event' => $event,
        ]);
        $this->mailService->sendEmail($email);

        $this->addFlash('success', 'Inscription réussie.');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id}/unregister', name: 'app_event_unregister', methods: ['GET', 'POST'])]
    public function unregister(Request $request, Event $event): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour vous désinscrire');

            return $this->redirectToRoute('app_login');
        }

        $userRegistered = $this->eventParticipantsRepository->findOneBy(['user' => $user, 'event' => $event]);

        if ($userRegistered == null) {
            $this->addFlash('danger', 'Vous n\'êtes pas inscrit à cet événement.');

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $this->em->remove($userRegistered);
        $this->em->flush();

        $email = new ParticipantUnRegistrationEvent($user->getEmail());
        $email->setParams([
            'user' => $user,
            'event' => $event,
        ]);
        $this->mailService->sendEmail($email);

        $this->addFlash('success', 'Désinscription réussie.');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}

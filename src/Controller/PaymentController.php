<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipants;
use App\Enum\PaymentStatusEnum;
use App\Event\Mail\ParticipantRegistrationEvent;
use App\Event\Mail\ParticipantPaymentFailEvent;
use App\Service\MailService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{

    public function __construct(
        private readonly StripeService $stripeService,
        private readonly EntityManagerInterface $em,
        private readonly MailService $mailService,
    )
    {
    }

    #[Route('/pay/{id}', name: 'app_pay_event')]
    public function createCheckoutSession(Event $event): Response
    {
        $checkout_session = $this->stripeService->createCheckoutSession($event, $this->generateUrl('payment_success', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL), $this->generateUrl('payment_cancel', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/success/{id}', name: 'payment_success')]
    public function success(Event $event): Response
    {
        $user = $this->getUser();

        $eventParticipant = new EventParticipants();
        $eventParticipant->setEvent($event);
        $eventParticipant->setUser($user);
        $eventParticipant->setHasPaid(true);
        $eventParticipant->setPaymentStatus(PaymentStatusEnum::SUCCESS);

        $this->em->persist($eventParticipant);
        $this->em->flush();

        $email = new ParticipantRegistrationEvent($user->getEmail());
        $email->setParams([
            'user'  => $user,
            'event' => $event,
        ]);
        $this->mailService->sendEmail($email);

        $this->addFlash('success', 'Paiement effectué avec succès ! Vous êtes inscrit à l\'événement.');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/cancel/{id}', name: 'payment_cancel')]
    public function cancel(Event $event): Response
    {
        $user = $this->getUser();

        $eventParticipant = new EventParticipants();
        $eventParticipant->setEvent($event);
        $eventParticipant->setUser($user);
        $eventParticipant->setHasPaid(false);
        $eventParticipant->setPaymentStatus(PaymentStatusEnum::FAILED);

        $this->em->persist($eventParticipant);
        $this->em->flush();

        $email = new ParticipantPaymentFailEvent($user->getEmail());
        $email->setParams([
            'user'  => $user,
            'event' => $event,
        ]);
        $this->mailService->sendEmail($email);

        $this->addFlash('error', 'Paiement annulé.');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/webhook_stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handleStripeWebhook(Request $request): Response
    {
        $webhook = $this->stripeService->getWebhooks($request);
        if (isset($webhook['error'])) {
            $this->addFlash('error', $webhook['error']);
        }

        switch ($webhook['status']) {
            case 'success':
                $this->addFlash('success', $webhook['status']);
                break;
            case 'fail':
                $this->addFlash('danger', $webhook['status']);
                break;
            case 'processing':
                $this->addFlash('info', $webhook['status']);
                break;
            default:
                $this->addFlash('warning', $webhook['status']);
                break;
        }

        return new Response('Webhook handled', Response::HTTP_OK);
    }
}

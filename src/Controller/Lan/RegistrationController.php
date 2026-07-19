<?php

namespace App\Controller\Lan;

use App\Exception\Lan\LanRegistrationException;
use App\Form\Lan\ParticipantRegistrationType;
use App\Repository\ParticipantRepository;
use App\Service\Lan\ParticipantRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Enum\RegistrationStatusEnum;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/registration')]
final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[Route('', name: 'app_registration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantRegistrationService $registrationService): Response
    {
        $form = $this->createForm(ParticipantRegistrationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $participant = $registrationService->register(
                    $data['firstname'],
                    $data['lastname'],
                    $data['email'],
                    $form->get('password')->getData(),
                    $data['discordPseudo'] ?? null,
                    (bool) $data['isMajorConfirmed'],
                );

                $user = $participant->getUser();
                if ($user !== null) {
                    $this->security->login($user, 'form_login', 'main');
                }

                return $this->redirectToRoute('app_registration_payment_pending', [
                    'internalReference' => $participant->getInternalReference(),
                ]);
            } catch (LanRegistrationException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('lan/registration/new.html.twig', [
            'form' => $form->createView(),
            'passwordStrength' => $_ENV['PASSWORD_STRENGTH_VALUE'],
        ]);
    }

    #[Route('/{internalReference}/payment-pending', name: 'app_registration_payment_pending', methods: ['GET'])]
    public function pending(string $internalReference, ParticipantRepository $participantRepository, TranslatorInterface $translator): Response
    {
        $participant = $participantRepository->findOneBy(['internalReference' => $internalReference]);

        if ($participant === null) {
            throw $this->createNotFoundException($translator->trans('registration.error.not_found'));
        }
        if ($this->getUser() === null || $participant->getUser()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('lan/registration/pending.html.twig', [
            'participant' => $participant,
            'helloAssoWidgetUrl' => $_ENV['HELLOASSO_WIDGET_URL'] ?? '',
            'helloAssoPublicUrl' => $_ENV['HELLOASSO_PUBLIC_URL'] ?? '',
        ]);
    }
    #[Route('/{internalReference}/payment-status', name: 'app_registration_payment_status', methods: ['GET'])]
    public function paymentStatus(string $internalReference, ParticipantRepository $participantRepository): JsonResponse
    {
        $participant = $participantRepository->findOneBy(['internalReference' => $internalReference]);
        if ($participant === null || $this->getUser() === null || $participant->getUser()?->getId() !== $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }
        $paid = $participant->getRegistrationStatus() === RegistrationStatusEnum::PAID;
        return new JsonResponse([
            'status' => $participant->getRegistrationStatus()->value,
            'paid' => $paid,
            'redirectUrl' => $paid ? $this->generateUrl('app_lan_profile') : null,
            'updatedAt' => ($participant->getPaidAt() ?? $participant->getCreatedAt())?->format(DATE_ATOM),
        ]);
    }
}

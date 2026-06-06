<?php

namespace App\Controller\Lan;

use App\Exception\Lan\LanRegistrationException;
use App\Form\Lan\ParticipantRegistrationType;
use App\Repository\ParticipantRepository;
use App\Service\Lan\ParticipantRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/lan/inscription')]
final class RegistrationController extends AbstractController
{
    #[Route('', name: 'app_lan_registration_new', methods: ['GET', 'POST'])]
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
                    $data['subscription'],
                );

                return $this->redirectToRoute('app_lan_registration_pending', [
                    'internalReference' => $participant->getInternalReference(),
                ]);
            } catch (LanRegistrationException $exception) {
                $this->addFlash('danger', $exception->getMessage());
            }
        }

        return $this->render('lan/registration/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{internalReference}/pending', name: 'app_lan_registration_pending', methods: ['GET'])]
    public function pending(string $internalReference, ParticipantRepository $participantRepository): Response
    {
        $participant = $participantRepository->findOneBy(['internalReference' => $internalReference]);

        if ($participant === null) {
            throw $this->createNotFoundException('Inscription introuvable.');
        }

        return $this->render('lan/registration/pending.html.twig', [
            'participant' => $participant,
        ]);
    }
}

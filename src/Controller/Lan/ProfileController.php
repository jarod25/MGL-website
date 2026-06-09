<?php

namespace App\Controller\Lan;

use App\Entity\Participant;
use App\Entity\User;
use App\Enum\RegistrationStatusEnum;
use App\Repository\ParticipantRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProfileController extends AbstractController
{
    #[Route('/my-lan', name: 'app_lan_profile', methods: ['GET'])]
    public function show(
        ParticipantRepository $participantRepository,
        TeamRepository $teamRepository,
        TeamMemberRepository $teamMemberRepository,
        TranslatorInterface $translator,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $participant = $participantRepository->findOneByUser($user);

        if (!$participant instanceof Participant) {
            $this->addFlash('danger', $translator->trans('lan_profile.error.participant_required'));

            return $this->redirectToRoute('app_registration_new');
        }

        $captainTeams = $teamRepository->findByCaptain($participant);
        $memberTeams = $teamMemberRepository->findByParticipant($participant);
        $memberCounts = [];

        foreach ($captainTeams as $team) {
            $teamId = $team->getId();

            if ($teamId !== null) {
                $memberCounts[$teamId] = $teamMemberRepository->countByTeam($team);
            }
        }

        foreach ($memberTeams as $teamMember) {
            $team = $teamMember->getTeam();
            $teamId = $team?->getId();

            if ($team !== null && $teamId !== null && !isset($memberCounts[$teamId])) {
                $memberCounts[$teamId] = $teamMemberRepository->countByTeam($team);
            }
        }

        return $this->render('lan/profile/show.html.twig', [
            'participant' => $participant,
            'captainTeams' => $captainTeams,
            'memberTeams' => $memberTeams,
            'memberCounts' => $memberCounts,
            'isPaid' => $participant->getRegistrationStatus() === RegistrationStatusEnum::PAID,
        ]);
    }
}

<?php

namespace App\Controller\Lan;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\User;
use App\Exception\Lan\LanRegistrationException;
use App\Form\Lan\TeamMemberType;
use App\Form\Lan\TeamType;
use App\Repository\GameRepository;
use App\Repository\ParticipantRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;
use App\Service\Lan\TeamRegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TeamController extends AbstractController
{
    #[Route('/teams', name: 'app_team_index', methods: ['GET'])]
    public function index(
        GameRepository $gameRepository,
        TeamRepository $teamRepository,
        TeamMemberRepository $teamMemberRepository,
    ): Response {
        $games = $gameRepository->findActiveOrdered();
        $teamsByGame = [];
        $memberCounts = [];

        foreach ($games as $game) {
            $teams = $teamRepository->findByGame($game);
            $teamsByGame[$game->getId()] = $teams;

            foreach ($teams as $team) {
                $memberCounts[$team->getId()] = $teamMemberRepository->countByTeam($team);
            }
        }

        return $this->render('lan/team/index.html.twig', [
            'games' => $games,
            'teamsByGame' => $teamsByGame,
            'memberCounts' => $memberCounts,
        ]);
    }

    #[Route('/teams/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        ParticipantRepository $participantRepository,
        TeamRegistrationService $teamRegistrationService,
        TranslatorInterface $translator,
    ): Response {
        $participant = $this->getConnectedParticipant($participantRepository, $translator);
        if (!$participant instanceof Participant) {
            return $this->redirectToParticipantRegistration();
        }

        $form = $this->createForm(TeamType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $game = $data['game'] ?? null;

            if ($game instanceof Game) {
                try {
                    $team = $teamRegistrationService->createTeam(
                        $game,
                        $participant,
                        (string) $data['name'],
                        (string) $data['captainInGamePseudo'],
                    );

                    $this->addFlash('success', $translator->trans('team.flash.created'));

                    return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
                } catch (LanRegistrationException $exception) {
                    $this->addFlash('danger', $translator->trans($exception->getMessage()));
                } catch (\Throwable) {
                    $this->addFlash('danger', $translator->trans('team.error.creation_failed'));
                }
            }
        }

        return $this->render('lan/team/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/teams/{id}', name: 'app_team_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(
        Team $team,
        ParticipantRepository $participantRepository,
        TeamMemberRepository $teamMemberRepository,
    ): Response {
        $participant = null;
        $user = $this->getUser();

        if ($user instanceof User) {
            $participant = $participantRepository->findOneByUser($user);
        }

        return $this->render('lan/team/show.html.twig', [
            'team' => $team,
            'members' => $teamMemberRepository->findByTeam($team),
            'canAddMembers' => $this->isCaptain($participant, $team),
        ]);
    }

    #[Route('/teams/{id}/members/new', name: 'app_team_member_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function newMember(
        Team $team,
        Request $request,
        ParticipantRepository $participantRepository,
        TeamMemberRepository $teamMemberRepository,
        TeamRegistrationService $teamRegistrationService,
        TranslatorInterface $translator,
    ): Response {
        $participant = $this->getConnectedParticipant($participantRepository, $translator);
        if (!$participant instanceof Participant) {
            return $this->redirectToParticipantRegistration();
        }

        if (!$this->isCaptain($participant, $team)) {
            $this->addFlash('danger', $translator->trans('team.error.only_captain_can_add_member'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        $form = $this->createForm(TeamMemberType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $targetParticipant = $participantRepository->findOneByEmailOrDiscordPseudo((string) $data['participantEmail']);

            if (!$targetParticipant instanceof Participant) {
                $form->get('participantEmail')->addError(new FormError($translator->trans('team.error.member_not_found')));
            } else {
                try {
                    $teamRegistrationService->addMember($team, $targetParticipant, (string) $data['inGamePseudo']);
                    $this->addFlash('success', $translator->trans('team.flash.member_added'));

                    return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
                } catch (LanRegistrationException $exception) {
                    $this->addFlash('danger', $translator->trans($exception->getMessage()));
                } catch (\Throwable) {
                    $this->addFlash('danger', $translator->trans('team.error.member_add_failed'));
                }
            }
        }

        return $this->render('lan/team/member_new.html.twig', [
            'team' => $team,
            'members' => $teamMemberRepository->findByTeam($team),
            'form' => $form->createView(),
        ]);
    }

    private function getConnectedParticipant(ParticipantRepository $participantRepository, TranslatorInterface $translator): ?Participant
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('danger', $translator->trans('team.error.login_required'));

            return null;
        }

        $participant = $participantRepository->findOneByUser($user);
        if (!$participant instanceof Participant) {
            $this->addFlash('danger', $translator->trans('team.error.participant_required'));

            return null;
        }

        return $participant;
    }

    private function redirectToParticipantRegistration(): Response
    {
        if (!$this->getUser() instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_registration_new');
    }

    private function isCaptain(?Participant $participant, Team $team): bool
    {
        $captain = $team->getCaptain();

        return $participant instanceof Participant
            && $captain instanceof Participant
            && $participant->getId() === $captain->getId();
    }
}

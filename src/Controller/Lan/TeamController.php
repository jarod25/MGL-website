<?php

namespace App\Controller\Lan;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Enum\GameDayEnum;
use App\Exception\Lan\LanRegistrationException;
use App\Form\Lan\TeamMemberType;
use App\Form\Lan\TeamType;
use App\Repository\GameRepository;
use App\Repository\ParticipantRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;
use App\Service\Lan\TeamRegistrationService;
use App\Service\Lan\TeamManagementService;
use App\Service\PublicImageResolver;
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
        PublicImageResolver $publicImageResolver,
    ): Response {
        $games = $this->orderGamesForTeamIndex($gameRepository->findActiveOrdered());

        return $this->render('lan/team/index.html.twig', [
            'gamesByDay' => $this->groupGamesByDay($games),
            'teamCounts' => $teamRepository->countGroupedByGames($games),
            'gameLogos' => $this->getAvailableGameLogos($publicImageResolver),
        ]);
    }

    /**
     * @param Game[] $games
     * @return Game[]
     */
    private function orderGamesForTeamIndex(array $games): array
    {
        $slugOrder = [
            'league-of-legends' => 10,
            'rocket-league' => 20,
            'valorant' => 30,
            'ea-fc-26' => 40,
        ];

        usort($games, static function (Game $first, Game $second) use ($slugOrder): int {
            return [$first->getDay()->value, $slugOrder[$first->getSlug()] ?? 100, $first->getName()]
                <=> [$second->getDay()->value, $slugOrder[$second->getSlug()] ?? 100, $second->getName()];
        });

        return $games;
    }

    /**
     * @param Game[] $games
     * @return array<string, Game[]>
     */
    private function groupGamesByDay(array $games): array
    {
        $gamesByDay = [
            GameDayEnum::SATURDAY->value => [],
            GameDayEnum::SUNDAY->value => [],
        ];

        foreach ($games as $game) {
            $gamesByDay[$game->getDay()->value][] = $game;
        }

        return array_filter($gamesByDay);
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableGameLogos(PublicImageResolver $publicImageResolver): array
    {
        $logos = [];

        foreach (['league-of-legends', 'rocket-league', 'valorant', 'ea-fc-26'] as $slug) {
            $path = $publicImageResolver->resolve('images/games', $slug);
            if ($path !== null) {
                $logos[$slug] = $path;
            }
        }

        return $logos;
    }

    #[Route('/teams/game/{slug}', name: 'app_team_game', methods: ['GET'])]
    public function game(
        string $slug,
        GameRepository $gameRepository,
        TeamRepository $teamRepository,
        TeamMemberRepository $teamMemberRepository,
        TranslatorInterface $translator,
    ): Response {
        $game = $gameRepository->findOneActiveBySlug($slug);

        if (!$game instanceof Game) {
            throw $this->createNotFoundException($translator->trans('team.game.not_found'));
        }

        $teams = $teamRepository->findByGame($game);

        return $this->render('lan/team/game.html.twig', [
            'game' => $game,
            'teams' => $teams,
            'teamCount' => count($teams),
            'memberCounts' => $teamMemberRepository->countGroupedByTeams($teams),
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

        $members = $teamMemberRepository->findByTeam($team);
        $memberCount = $teamMemberRepository->countByTeam($team);
        $maxPlayers = $team->getGame()?->getMaxPlayersPerTeam();
        $isTeamFull = $maxPlayers !== null && $maxPlayers > 0 && $memberCount >= $maxPlayers;
        $isCaptain = $this->isCaptain($participant, $team);

        return $this->render('lan/team/show.html.twig', [
            'team' => $team,
            'members' => $members,
            'memberCount' => $memberCount,
            'maxPlayers' => $maxPlayers,
            'isTeamFull' => $isTeamFull,
            'isCaptain' => $isCaptain,
            'canAddMembers' => $isCaptain && !$isTeamFull,
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

    #[Route('/teams/{id}/delete', name: 'app_team_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Team $team,
        Request $request,
        ParticipantRepository $participantRepository,
        TeamManagementService $teamManagementService,
        TranslatorInterface $translator,
    ): Response {
        $participant = $this->getConnectedParticipant($participantRepository, $translator);
        if (!$participant instanceof Participant) {
            return $this->redirectToParticipantRegistration();
        }

        if (!$this->isCaptain($participant, $team)) {
            $this->addFlash('danger', $translator->trans('team.error.only_captain_can_delete_team'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        if (!$this->isCsrfTokenValid('delete_team_'.$team->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', $translator->trans('team.error.invalid_delete_token'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        try {
            $teamManagementService->deleteTeam($team);

            $this->addFlash('success', $translator->trans('team.flash.deleted'));

            return $this->redirectToRoute('app_team_index');
        } catch (\Throwable) {
            $this->addFlash('danger', $translator->trans('team.error.delete_failed'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }
    }


    #[Route('/teams/{teamId}/members/{memberId}/delete', name: 'app_team_member_delete', requirements: ['teamId' => '\\d+', 'memberId' => '\\d+'], methods: ['POST'])]
    public function deleteMember(
        int $teamId,
        int $memberId,
        Request $request,
        TeamRepository $teamRepository,
        TeamMemberRepository $teamMemberRepository,
        ParticipantRepository $participantRepository,
        TeamManagementService $teamManagementService,
        TranslatorInterface $translator,
    ): Response {
        $team = $teamRepository->find($teamId);
        $member = $teamMemberRepository->find($memberId);

        if (!$team instanceof Team || !$member instanceof TeamMember) {
            throw $this->createNotFoundException($translator->trans('team.error.member_not_found'));
        }

        if (!$this->isCsrfTokenValid('delete_team_member_'.$member->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $participant = $user instanceof User ? $participantRepository->findOneByUser($user) : null;

        if (!$this->isCaptain($participant, $team)) {
            $this->addFlash('danger', $translator->trans('team.error.only_captain_can_remove_member'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        if ($member->getTeam()?->getId() !== $team->getId()) {
            $this->addFlash('danger', $translator->trans('team.error.member_does_not_belong_to_team'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        $captain = $team->getCaptain();
        if ($captain instanceof Participant && $member->getParticipant()?->getId() === $captain->getId()) {
            $this->addFlash('danger', $translator->trans('team.error.cannot_remove_captain'));

            return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
        }

        try {
            $teamManagementService->removeMember($team, $member);

            $this->addFlash('success', $translator->trans('team.flash.member_removed'));
        } catch (\Throwable) {
            $this->addFlash('danger', $translator->trans('team.error.member_remove_failed'));
        }

        return $this->redirectToRoute('app_team_show', ['id' => $team->getId()]);
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

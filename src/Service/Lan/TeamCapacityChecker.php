<?php

namespace App\Service\Lan;

use App\Entity\Game;
use App\Entity\Team;
use App\Exception\Lan\LanRegistrationException;
use App\Repository\TeamMemberRepository;
use App\Repository\TeamRepository;

final class TeamCapacityChecker
{
    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly TeamMemberRepository $teamMemberRepository,
    ) {
    }

    public function assertGameHasSlot(Game $game): void
    {
        if (!$game->isActive()) {
            throw new LanRegistrationException('team.error.game_not_active');
        }

        $maxTeams = $game->getMaxTeams();
        if ($maxTeams === null || $maxTeams <= 0) {
            throw new LanRegistrationException('team.error.game_max_teams_invalid');
        }

        if ($this->teamRepository->countByGame($game) >= $maxTeams) {
            throw new LanRegistrationException('team.error.game_max_teams_reached');
        }
    }

    public function assertTeamHasSlot(Team $team): void
    {
        $game = $team->getGame();
        if ($game === null) {
            throw new LanRegistrationException('team.error.team_has_no_game');
        }

        if (!$game->isActive()) {
            throw new LanRegistrationException('team.error.game_not_active');
        }

        $maxPlayers = $game->getMaxPlayersPerTeam();
        if ($maxPlayers === null || $maxPlayers <= 0) {
            throw new LanRegistrationException('team.error.game_max_players_invalid');
        }

        if ($this->teamMemberRepository->countByTeam($team) >= $maxPlayers) {
            throw new LanRegistrationException('team.error.team_max_players_reached');
        }
    }
}

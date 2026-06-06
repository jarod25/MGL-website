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
            throw new LanRegistrationException('Game is not active.');
        }

        $maxTeams = $game->getMaxTeams();
        if ($maxTeams === null || $maxTeams <= 0) {
            throw new LanRegistrationException('Game max teams is invalid.');
        }

        if ($this->teamRepository->countByGame($game) >= $maxTeams) {
            throw new LanRegistrationException('Game has reached max teams capacity.');
        }
    }

    public function assertTeamHasSlot(Team $team): void
    {
        $game = $team->getGame();
        if ($game === null) {
            throw new LanRegistrationException('Team has no game assigned.');
        }

        if (!$game->isActive()) {
            throw new LanRegistrationException('Game is not active.');
        }

        $maxPlayers = $game->getMaxPlayersPerTeam();
        if ($maxPlayers === null || $maxPlayers <= 0) {
            throw new LanRegistrationException('Game max players per team is invalid.');
        }

        if ($this->teamMemberRepository->countByTeam($team) >= $maxPlayers) {
            throw new LanRegistrationException('Team has reached max players capacity.');
        }
    }
}

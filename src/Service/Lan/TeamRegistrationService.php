<?php

namespace App\Service\Lan;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Exception\Lan\LanRegistrationException;
use App\Repository\TeamMemberRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TeamRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParticipantAccessChecker $participantAccessChecker,
        private readonly TeamCapacityChecker $teamCapacityChecker,
        private readonly TeamMemberRepository $teamMemberRepository,
        private readonly TeamSlugGenerator $teamSlugGenerator,
    ) {
    }

    public function createTeam(Game $game, Participant $captain, string $name, string $captainInGamePseudo): Team
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $this->participantAccessChecker->assertCanJoinGame($captain, $game);
            $this->teamCapacityChecker->assertGameHasSlot($game);

            if ($this->teamMemberRepository->findOneByParticipantAndGame($captain, $game) !== null) {
                throw new LanRegistrationException('team.error.captain_already_registered_for_game');
            }

            $team = (new Team())
                ->setGame($game)
                ->setCaptain($captain)
                ->setName($name)
                ->setSlug($this->teamSlugGenerator->generate($game, $name))
                ->setCreatedAt(new \DateTimeImmutable());

            $captainMember = (new TeamMember())
                ->setTeam($team)
                ->setParticipant($captain)
                ->setGame($game)
                ->setInGamePseudo($captainInGamePseudo)
                ->setJoinedAt(new \DateTimeImmutable());

            $this->entityManager->persist($team);
            $this->entityManager->persist($captainMember);
            $this->entityManager->flush();
            $connection->commit();

            return $team;
        } catch (\Throwable $throwable) {
            $connection->rollBack();
            throw $throwable;
        }
    }

    public function addMember(Team $team, Participant $participant, string $inGamePseudo): TeamMember
    {
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $game = $team->getGame();
            if ($game === null) {
                throw new LanRegistrationException('team.error.team_has_no_game');
            }

            $this->participantAccessChecker->assertCanJoinGame($participant, $game);

            if ($this->teamMemberRepository->findOneByParticipantAndGame($participant, $game) !== null) {
                throw new LanRegistrationException('team.error.participant_already_registered_for_game');
            }

            $this->teamCapacityChecker->assertTeamHasSlot($team);

            $teamMember = (new TeamMember())
                ->setTeam($team)
                ->setParticipant($participant)
                ->setGame($game)
                ->setInGamePseudo($inGamePseudo)
                ->setJoinedAt(new \DateTimeImmutable());

            $this->entityManager->persist($teamMember);
            $this->entityManager->flush();
            $connection->commit();

            return $teamMember;
        } catch (\Throwable $throwable) {
            $connection->rollBack();
            throw $throwable;
        }
    }
}

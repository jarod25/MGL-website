<?php

namespace App\Service\Lan;

use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Repository\TeamMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final class TeamManagementService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TeamMemberRepository $teamMemberRepository,
    ) {
    }

    public function removeMember(Team $team, TeamMember $member): void
    {
        if ($member->getTeam()?->getId() !== $team->getId()) {
            throw new InvalidArgumentException('team.error.member_does_not_belong_to_team');
        }

        $participant = $member->getParticipant();
        $captain = $team->getCaptain();
        if ($participant instanceof Participant && $captain instanceof Participant && $participant->getId() === $captain->getId()) {
            throw new InvalidArgumentException('team.error.cannot_remove_captain');
        }

        $this->entityManager->wrapInTransaction(function () use ($member, $participant): void {
            if ($participant instanceof Participant && $this->teamMemberRepository->countByParticipant($participant) <= 1) {
                $participant->setLockedDay(null);
            }

            $this->entityManager->remove($member);
        });
    }

    public function deleteTeam(Team $team): void
    {
        $members = $this->teamMemberRepository->findByTeam($team);

        $this->entityManager->wrapInTransaction(function () use ($team, $members): void {
            foreach ($members as $member) {
                $participant = $member->getParticipant();
                if ($participant instanceof Participant && $this->teamMemberRepository->countByParticipantExcludingTeam($participant, $team) === 0) {
                    $participant->setLockedDay(null);
                }

                $this->entityManager->remove($member);
            }

            $this->entityManager->remove($team);
        });
    }
}

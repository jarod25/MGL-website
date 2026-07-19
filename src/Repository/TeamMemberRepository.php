<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use App\Entity\TeamMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMember>
 */
class TeamMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    public function countByTeam(Team $team): int
    {
        return (int) $this->createQueryBuilder('tm')
            ->select('COUNT(tm.id)')
            ->andWhere('tm.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneByParticipantAndGame(Participant $participant, Game $game): ?TeamMember
    {
        return $this->findOneBy([
            'participant' => $participant,
            'game' => $game,
        ]);
    }

    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('tm')
            ->andWhere('tm.team = :team')
            ->setParameter('team', $team)
            ->orderBy('tm.joinedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return TeamMember[]
     */
    public function findByParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('tm')
            ->addSelect('t', 'g', 'c')
            ->join('tm.team', 't')
            ->join('tm.game', 'g')
            ->join('t.captain', 'c')
            ->andWhere('tm.participant = :participant')
            ->setParameter('participant', $participant)
            ->orderBy('tm.joinedAt', 'DESC')
            ->addOrderBy('g.day', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return TeamMember[]
     */
    public function findAllForAdminExport(): array
    {
        return $this->createQueryBuilder('tm')
            ->addSelect('p', 't', 'g', 'c', 's')
            ->join('tm.participant', 'p')
            ->leftJoin('p.subscription', 's')
            ->join('tm.team', 't')
            ->join('tm.game', 'g')
            ->join('t.captain', 'c')
            ->orderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->addOrderBy('p.email', 'ASC')
            ->addOrderBy('g.day', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByParticipant(Participant $participant): int
    {
        return (int) $this->createQueryBuilder('tm')
            ->select('COUNT(tm.id)')
            ->andWhere('tm.participant = :participant')
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByParticipantExcludingTeam(Participant $participant, Team $team): int
    {
        return (int) $this->createQueryBuilder('tm')
            ->select('COUNT(tm.id)')
            ->andWhere('tm.participant = :participant')
            ->andWhere('tm.team != :team')
            ->setParameter('participant', $participant)
            ->setParameter('team', $team)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Team[] $teams
     * @return array<int, int>
     */
    public function countGroupedByTeams(array $teams): array
    {
        if ($teams === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('tm')
            ->select('IDENTITY(tm.team) AS team_id, COUNT(tm.id) AS members_count')
            ->andWhere('tm.team IN (:teams)')
            ->setParameter('teams', $teams)
            ->groupBy('tm.team')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['team_id']] = (int) $row['members_count'];
        }

        return $counts;
    }
}

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

}

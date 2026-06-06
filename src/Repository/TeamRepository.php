<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Participant;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function countByGame(Game $game): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByGame(Game $game): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.game = :game')
            ->setParameter('game', $game)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Team[]
     */
    public function findByCaptain(Participant $captain): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.captain = :captain')
            ->setParameter('captain', $captain)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Participant;
use App\Enum\GameDayEnum;
use Doctrine\ORM\QueryBuilder;
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

    public function findOneByGameAndSlug(Game $game, string $slug): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.game = :game')
            ->andWhere('t.slug = :slug')
            ->setParameter('game', $game)
            ->setParameter('slug', trim($slug))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function slugExistsForGame(Game $game, string $slug): bool
    {
        return $this->findOneByGameAndSlug($game, $slug) instanceof Team;
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
     * @param Game[] $games
     * @return array<int, int>
     */
    public function countGroupedByGames(array $games): array
    {
        if ($games === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.game) AS game_id, COUNT(t.id) AS teams_count')
            ->andWhere('t.game IN (:games)')
            ->setParameter('games', $games)
            ->groupBy('t.game')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['game_id']] = (int) $row['teams_count'];
        }

        return $counts;
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


    public function createAdminListQueryBuilder(?string $search, ?Game $game, ?GameDayEnum $day): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->addSelect('g', 'c')
            ->join('t.game', 'g')
            ->join('t.captain', 'c')
            ->orderBy('t.createdAt', 'DESC');

        if ($search !== null && trim($search) !== '') {
            $normalizedSearch = '%'.mb_strtolower(trim($search)).'%';
            $queryBuilder
                ->andWhere('LOWER(t.name) LIKE :search OR LOWER(c.firstname) LIKE :search OR LOWER(c.lastname) LIKE :search OR LOWER(c.email) LIKE :search OR LOWER(c.discordPseudo) LIKE :search')
                ->setParameter('search', $normalizedSearch);
        }

        if ($game instanceof Game) {
            $queryBuilder
                ->andWhere('t.game = :game')
                ->setParameter('game', $game);
        }

        if ($day instanceof GameDayEnum) {
            $queryBuilder
                ->andWhere('g.day = :day')
                ->setParameter('day', $day);
        }

        return $queryBuilder;
    }
}

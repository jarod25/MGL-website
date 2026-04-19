<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Model\EventSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAvailableEvents()
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isPublic = :isPublic')
            ->andWhere('e.nbMaxParticipants > :nbMaxParticipants')
            ->andWhere('SIZE(e.participants) < e.nbMaxParticipants')
            ->setParameter('isPublic', 1)
            ->setParameter('nbMaxParticipants', 1)
            ->getQuery()
            ->getResult();
    }

    public function findBySearchCriteria(EventSearch $search, $user = null)
    {
        $qb = $this->createQueryBuilder('e');

        if ($search->title !== null) {
            $qb->andWhere('e.title LIKE :title')
                ->setParameter('title', '%' . $search->title . '%');
        }
        if ($search->startDate !== null) {
            $qb->andWhere('e.startDate >= :startDate')
                ->setParameter('startDate', $search->startDate->format('Y-m-d'));
        }
        if ($user !== null) {
            if ($search->isPublic !== null) {
                $qb->andWhere('e.isPublic = :isPublic')
                    ->setParameter('isPublic', $search->isPublic);
            }
        } else {
            $qb->andWhere('e.isPublic = :isPublic')
                ->setParameter('isPublic', 1);
        }

        if ($search->isFull !== null) {
            if ($search->isFull === true) {
                $qb->andWhere('SIZE(e.participants) < e.nbMaxParticipants');
            } else {
                $qb->andWhere('SIZE(e.participants) = e.nbMaxParticipants');
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findEventsByUser(User $user)
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.participants', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

}

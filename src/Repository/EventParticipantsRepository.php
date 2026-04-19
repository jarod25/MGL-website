<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventParticipants;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventParticipants>
 *
 * @method EventParticipants|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventParticipants|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventParticipants[]    findAll()
 * @method EventParticipants[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventParticipantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventParticipants::class);
    }
}

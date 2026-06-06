<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function findOneByUser(User $user): ?Participant
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function findOneByEmail(string $email): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.email) = :email')
            ->setParameter('email', mb_strtolower(trim($email)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByEmailOrDiscordPseudo(string $identifier): ?Participant
    {
        $normalizedIdentifier = mb_strtolower(trim($identifier));

        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.email) = :identifier OR LOWER(p.discordPseudo) = :identifier')
            ->setParameter('identifier', $normalizedIdentifier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
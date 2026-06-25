<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\Subscription;
use App\Entity\User;
use App\Enum\RegistrationStatusEnum;
use Doctrine\ORM\QueryBuilder;
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

    public function findOneByEmailOrInternalReference(string $identifier): ?Participant
    {
        $normalizedIdentifier = mb_strtolower(trim($identifier));

        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.email) = :identifier OR LOWER(p.internalReference) = :identifier')
            ->setParameter('identifier', $normalizedIdentifier)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function createAdminListQueryBuilder(
        ?string $search,
        ?RegistrationStatusEnum $status,
        ?Subscription $subscription,
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect('s')
            ->leftJoin('p.subscription', 's')
            ->orderBy('p.createdAt', 'DESC');

        if ($search !== null && trim($search) !== '') {
            $normalizedSearch = '%'.mb_strtolower(trim($search)).'%';
            $queryBuilder
                ->andWhere('LOWER(p.firstname) LIKE :search OR LOWER(p.lastname) LIKE :search OR LOWER(p.email) LIKE :search OR LOWER(p.discordPseudo) LIKE :search OR LOWER(p.internalReference) LIKE :search')
                ->setParameter('search', $normalizedSearch);
        }

        if ($status instanceof RegistrationStatusEnum) {
            $queryBuilder
                ->andWhere('p.registrationStatus = :status')
                ->setParameter('status', $status);
        }

        if ($subscription instanceof Subscription) {
            $queryBuilder
                ->andWhere('p.subscription = :subscription')
                ->setParameter('subscription', $subscription);
        }

        return $queryBuilder;
    }
}

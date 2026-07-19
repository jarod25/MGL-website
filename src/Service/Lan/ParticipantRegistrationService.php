<?php


namespace App\Service\Lan;

use App\Entity\Participant;
use App\Entity\Subscription;
use App\Entity\User;
use App\Enum\RegistrationStatusEnum;
use App\Exception\Lan\LanRegistrationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ParticipantRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository              $userRepository,
    )
    {
    }

    public function register(
        string       $firstname,
        string       $lastname,
        string       $email,
        string       $plainPassword,
        ?string      $discordPseudo,
        bool         $isMajorConfirmed,
    ): Participant
    {
        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            throw new LanRegistrationException('registration.error.email_already_used');
        }

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            $user = (new User())
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($email)
                ->setRoles(['ROLE_USER']);

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

            $participant = (new Participant())
                ->setUser($user)
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($email)
                ->setDiscordPseudo($discordPseudo)
                ->setIsMajorConfirmed($isMajorConfirmed)
                ->setSubscription(null)
                ->setRegistrationStatus(RegistrationStatusEnum::PENDING_PAYMENT)
                ->setInternalReference(bin2hex(random_bytes(16)))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setPaidAt(null)
                ->setLockedDay(null);

            $this->entityManager->persist($user);
            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            $connection->commit();

            return $participant;
        } catch (\Throwable $throwable) {
            $connection->rollBack();
            throw $throwable;
        }
    }
}

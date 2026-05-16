<?php

namespace App\Entity;

use App\Enum\GameDayEnum;
use App\Enum\RegistrationStatusEnum;
use App\Repository\ParticipantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'lan__participant')]
#[ORM\UniqueConstraint(name: 'uniq_lan_participant_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uniq_lan_participant_internal_reference', columns: ['internal_reference'])]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discordPseudo = null;

    #[ORM\Column]
    private bool $isMajorConfirmed = false;

    #[ORM\Column(type: Types::STRING, enumType: RegistrationStatusEnum::class)]
    private RegistrationStatusEnum $registrationStatus = RegistrationStatusEnum::PENDING_PAYMENT;

    #[ORM\Column(length: 64)]
    private ?string $internalReference = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Subscription $subscription = null;

    #[ORM\Column(type: Types::STRING, enumType: GameDayEnum::class, nullable: true)]
    private ?GameDayEnum $lockedDay = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDiscordPseudo(): ?string
    {
        return $this->discordPseudo;
    }

    public function setDiscordPseudo(?string $discordPseudo): static
    {
        $this->discordPseudo = $discordPseudo;

        return $this;
    }

    public function isMajorConfirmed(): bool
    {
        return $this->isMajorConfirmed;
    }

    public function setIsMajorConfirmed(bool $isMajorConfirmed): static
    {
        $this->isMajorConfirmed = $isMajorConfirmed;

        return $this;
    }

    public function getRegistrationStatus(): RegistrationStatusEnum
    {
        return $this->registrationStatus;
    }

    public function setRegistrationStatus(RegistrationStatusEnum $registrationStatus): static
    {
        $this->registrationStatus = $registrationStatus;

        return $this;
    }

    public function getInternalReference(): ?string
    {
        return $this->internalReference;
    }

    public function setInternalReference(string $internalReference): static
    {
        $this->internalReference = $internalReference;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getLockedDay(): ?GameDayEnum
    {
        return $this->lockedDay;
    }

    public function setLockedDay(?GameDayEnum $lockedDay): static
    {
        $this->lockedDay = $lockedDay;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }
}

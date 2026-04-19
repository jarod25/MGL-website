<?php

namespace App\Entity;

use App\Enum\PaymentStatusEnum;
use App\Repository\EventParticipantsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventParticipantsRepository::class)]
#[ORM\Table(name: 'events__participants')]
class EventParticipants
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'participants')]
    private Event $event;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participantsEvents')]
    private User $user;

    #[ORM\Column(type: 'string', nullable: true, enumType: PaymentStatusEnum::class)]
    private ?PaymentStatusEnum $paymentStatus = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hasPaid = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }


    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?PaymentStatusEnum $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function isHasPaid(): ?bool
    {
        return $this->hasPaid;
    }

    public function setHasPaid(bool $hasPaid): static
    {
        $this->hasPaid = $hasPaid;

        return $this;
    }
}

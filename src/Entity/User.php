<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user__user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'owner')]
    private Collection $events;

    #[ORM\OneToMany(targetEntity: EventParticipants::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private Collection $participantsEvents;

    #[ORM\Column(type: Types::ARRAY)]
    private array $roles = [];


    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->participantsEvents = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setOwner($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOwner() === $this) {
                $event->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEventParticipants(): Collection
    {
        return $this->participantsEvents;
    }

    public function addEventParticipant(EventParticipants $eventParticipant): static
    {
        if (!$this->participantsEvents->contains($eventParticipant)) {
            $this->participantsEvents[] = $eventParticipant;
            $eventParticipant->setUser($this);
        }

        return $this;
    }

    public function removeEventParticipant(EventParticipants $eventParticipant): static
    {
        $this->participantsEvents->removeElement($eventParticipant);
        return $this;
    }

    public function setRoles(array $roles): static
    {
        if (empty($this->roles) && empty($roles)) {
            $roles = ['ROLE_USER'];
        }
        $this->roles = $roles;

        return $this;
    }


    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function eraseCredentials()
    {

    }
}

<?php

namespace App\Entity;

use AllowDynamicProperties;
use App\Repository\EventRepository;
use App\Validator\IsValidDate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[AllowDynamicProperties]
#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'events__events')]
#[IsValidDate]
class Event
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir un titre')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Veuillez saisir une description')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Veuillez saisir une date de début d\'événement')]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Veuillez saisir une date de fin d\'événement')]
    private ?\DateTime $endDate = null;

    #[ORM\Column]
    #[Assert\GreaterThan(value: 0, message: 'Le nombre de participants doit être supérieur à 0')]
    #[Assert\NotBlank(message: 'Veuillez saisir un nombre de participants')]
    private ?int $nbMaxParticipants = null;

    #[ORM\Column]
    private ?bool $isPublic = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: EventParticipants::class, mappedBy: 'event', cascade: ['persist', 'remove'])]
    private Collection $participants;

    #[ORM\Column]
    private ?bool $isPayable = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThan(value: 0, message: 'Le prix doit être supérieur à 0')]
    #[Assert\NotBlank(message: 'Veuillez saisir un prix')]
    private ?int $price = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getNbMaxParticipants(): ?int
    {
        return $this->nbMaxParticipants;
    }

    public function setNbMaxParticipants(int $nbMaxParticipants): static
    {
        $this->nbMaxParticipants = $nbMaxParticipants;

        return $this;
    }

    public function isIsPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function isIsPayable(): ?bool
    {
        return $this->isPayable;
    }

    public function setIsPayable(bool $isPayable): static
    {
        $this->isPayable = $isPayable;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }
}

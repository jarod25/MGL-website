<?php

namespace App\Entity;

use App\Enum\GameDayEnum;
use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: 'lan__game')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?int $maxPlayersPerTeam = null;

    #[ORM\Column]
    private ?int $maxTeams = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::STRING, enumType: GameDayEnum::class)]
    private GameDayEnum $day;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMaxPlayersPerTeam(): ?int
    {
        return $this->maxPlayersPerTeam;
    }

    public function setMaxPlayersPerTeam(int $maxPlayersPerTeam): static
    {
        $this->maxPlayersPerTeam = $maxPlayersPerTeam;

        return $this;
    }

    public function getMaxTeams(): ?int
    {
        return $this->maxTeams;
    }

    public function setMaxTeams(int $maxTeams): static
    {
        $this->maxTeams = $maxTeams;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDay(): GameDayEnum
    {
        return $this->day;
    }

    public function setDay(GameDayEnum $day): static
    {
        $this->day = $day;

        return $this;
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Enum\GameDayEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['lan'];
    }

    public function load(ObjectManager $manager): void
    {
        $games = [
            ['name' => 'Rocket League', 'slug' => 'rocket-league', 'maxPlayersPerTeam' => 3, 'maxTeams' => 16, 'isActive' => true, 'day' => GameDayEnum::SATURDAY],
            ['name' => 'League of Legends', 'slug' => 'league-of-legends', 'maxPlayersPerTeam' => 5, 'maxTeams' => 16, 'isActive' => true, 'day' => GameDayEnum::SATURDAY],
            ['name' => 'Valorant', 'slug' => 'valorant', 'maxPlayersPerTeam' => 5, 'maxTeams' => 24, 'isActive' => true, 'day' => GameDayEnum::SUNDAY],
            ['name' => 'EA FC 26', 'slug' => 'ea-fc-26', 'maxPlayersPerTeam' => 1, 'maxTeams' => 100, 'isActive' => true, 'day' => GameDayEnum::SUNDAY],
        ];

        foreach ($games as $data) {
            $game = new Game();
            $game->setName($data['name']);
            $game->setSlug($data['slug']);
            $game->setMaxPlayersPerTeam($data['maxPlayersPerTeam']);
            $game->setMaxTeams($data['maxTeams']);
            $game->setIsActive($data['isActive']);
            $game->setDay($data['day']);

            $manager->persist($game);
        }

        $manager->flush();
    }
}
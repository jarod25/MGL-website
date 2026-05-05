<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $games = [
            ['name' => 'Rocket League', 'slug' => 'rocket-league', 'maxPlayersPerTeam' => 3, 'maxTeams' => 32, 'isActive' => true],
            ['name' => 'League of Legends', 'slug' => 'league-of-legends', 'maxPlayersPerTeam' => 5, 'maxTeams' => 16, 'isActive' => true],
            ['name' => 'Valorant', 'slug' => 'valorant', 'maxPlayersPerTeam' => 5, 'maxTeams' => 16, 'isActive' => true],
            ['name' => 'EA FC 26', 'slug' => 'ea-fc-26', 'maxPlayersPerTeam' => 1, 'maxTeams' => 64, 'isActive' => true],
        ];

        foreach ($games as $data) {
            $game = new Game();
            $game->setName($data['name']);
            $game->setSlug($data['slug']);
            $game->setMaxPlayersPerTeam($data['maxPlayersPerTeam']);
            $game->setMaxTeams($data['maxTeams']);
            $game->setIsActive($data['isActive']);

            $manager->persist($game);
        }

        $manager->flush();
    }
}

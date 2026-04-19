<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = $this->getReference('user_' . $i);
        }

        for ($i = 0; $i < 20; $i++) {
            $startDate = $faker->dateTimeBetween((new \DateTime('now'))->format('Y-m-d 09:00:00'), '2024-12-31 17:00:00');
            $startHour = rand(9, 12);
            $startDate->setTime($startHour, 00);

            $endDate = clone $startDate;
            $daysToAdd = rand(0, 7);

            if ($daysToAdd > 0) {
                $endDate->modify("+$daysToAdd days");
            }
            $endHour = rand(15,19);
            $endDate->setTime($endHour, 00);

            $bool = $faker->boolean(40);

            $event = new Event();
            $event->setTitle($faker->words(5, true));
            $event->setDescription($faker->words(50, true));
            $event->setStartDate($startDate);
            $event->setEndDate($endDate);
            $event->setIsPublic($faker->boolean(60));
            $event->setNbMaxParticipants($faker->randomNumber(2));
            $event->setOwner($users[array_rand($users)]);
            $event->setIsPayable($bool);
            if ($bool) {
                $event->setPrice($faker->randomFloat(2, 10, 100));
            }

            $manager->persist($event);
        }

        $manager->flush();
    }
}

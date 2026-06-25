<?php

namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['lan'];
    }

    public function load(ObjectManager $manager): void
    {
        $subscriptionRepository = $manager->getRepository(Subscription::class);

        foreach ($this->getSubscriptions() as $data) {
            $subscription = $subscriptionRepository->findOneBy(['slug' => $data['slug']]);

            if ($subscription instanceof Subscription) {
                continue;
            }

            $subscription = (new Subscription())
                ->setSlug($data['slug'])
                ->setName($data['name'])
                ->setHelloAssoTierId(null)
                ->setPrice($data['price'])
                ->setDurationDays($data['durationDays'])
                ->setIsActive(true);

            $manager->persist($subscription);
        }

        $manager->flush();
    }

    /**
     * @return array<int, array{name: string, slug: string, price: int, durationDays: int}>
     */
    private function getSubscriptions(): array
    {
        return [
            ['name' => 'Hommes 1 jour avec sandwich', 'slug' => 'men-one-day-with-meal', 'price' => 1500, 'durationDays' => 1],
            ['name' => 'Hommes 2 jours avec sandwich', 'slug' => 'men-two-days-with-meal', 'price' => 2700, 'durationDays' => 2],
            ['name' => 'Hommes 1 jour sans sandwich', 'slug' => 'men-one-day-without-meal', 'price' => 1300, 'durationDays' => 1],
            ['name' => 'Hommes 2 jours sans sandwich', 'slug' => 'men-two-days-without-meal', 'price' => 2300, 'durationDays' => 2],
            ['name' => 'Femmes 1 jour avec sandwich', 'slug' => 'women-one-day-with-meal', 'price' => 1300, 'durationDays' => 1],
            ['name' => 'Femmes 2 jours avec sandwich', 'slug' => 'women-two-days-with-meal', 'price' => 2300, 'durationDays' => 2],
            ['name' => 'Femmes 1 jour sans sandwich', 'slug' => 'women-one-day-without-meal', 'price' => 1100, 'durationDays' => 1],
            ['name' => 'Femmes 2 jours sans sandwich', 'slug' => 'women-two-days-without-meal', 'price' => 1900, 'durationDays' => 2],
        ];
    }
}

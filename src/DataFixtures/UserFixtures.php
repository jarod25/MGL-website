<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = [];
        for ($i = 0; $i < 5; $i++) {

            $email = $faker->safeEmail;
            $password = 'P@$$w0rd';

            $user = new User();
            $user->setFirstname($faker->firstName);
            $user->setLastname($faker->lastName);
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );
            $manager->persist($user);
            $this->addReference('user_' . $i, $user);
            $users[] = ['email' => $email, 'password' => $password];
        }

        $manager->flush();
        dump($users);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{

    public function testEditProfile(): void
    {
        $client = static::createClient();

        $em                 = static::getContainer()->get(EntityManagerInterface::class);
        $userPasswordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $userRepository     = static::getContainer()->get(UserRepository::class);

        $existingUser = $userRepository->findOneBy(['email' => 'test1@example.com']);
        if ($existingUser) {
            $em->remove($existingUser);
            $em->flush();
        }

        $user = new User();
        $user->setFirstname('Prenom 1');
        $user->setLastname('Nom 1');
        $user->setEmail('test1@example.com');
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $userPasswordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        $client->loginUser($user);

        $crawler = $client->request('GET', '/profil');
        $this->assertResponseIsSuccessful();

        $existingUpdatedUser = $userRepository->findOneBy(['email' => 'updated@example.com']);
        if ($existingUpdatedUser) {
            $em->remove($existingUpdatedUser);
            $em->flush();
        }

        $profileForm = $crawler->selectButton('Modifier mes informations')->form([
            'account[email]'     => 'updated@example.com',
            'account[firstname]' => 'UpdatedPrenom',
            'account[lastname]'  => 'UpdatedNom',
        ]);
        $client->submit($profileForm);


        $updatedUser = $userRepository->findOneBy(['email' => 'updated@example.com']);
        $this->assertNotNull($updatedUser);
        $client->loginUser($updatedUser);

        $crawler      = $client->request('GET', '/profil');
        $passwordForm = $crawler->selectButton('Modifier mon mot de passe')->form([
            'change_password[oldPassword]'      => 'password',
            'change_password[password][first]'  => 'newpassword123',
            'change_password[password][second]' => 'newpassword123',
        ]);

        $client->submit($passwordForm);
    }
}

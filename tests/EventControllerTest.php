<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EventControllerTest extends WebTestCase
{

    public function testCreateEvent()
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
        $crawler = $client->request('GET', '/evenements/creer-un-evenement');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Save')->form([
            'event[title]'             => 'Test Event',
            'event[description]'       => 'Test Event Description',
            'event[startDate]'         => '2024-07-01 09:00:00',
            'event[endDate]'           => '2024-07-10 17:00:00',
            'event[nbMaxParticipants]' => 50,
            'event[isPublic]'          => true,
            'event[isPayable]'         => false
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/evenements/');
    }

    public function testRegister()
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

        $client->request('GET', '/evenements/45/register');

        $this->assertResponseRedirects('/evenements/45');
    }
}

<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:user:grant-admin', description: 'Grant ROLE_ADMIN to an existing user.')]
final class GrantAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the existing user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = mb_strtolower(trim((string) $input->getArgument('email')));

        $user = $this->userRepository->createQueryBuilder('u')
            ->andWhere('LOWER(u.email) = :email')
            ->setParameter('email', $email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user instanceof User) {
            $io->error(sprintf('No user found for email "%s".', $email));

            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $io->info(sprintf('User "%s" already has ROLE_ADMIN.', $user->getEmail()));

            return Command::SUCCESS;
        }

        $roles[] = 'ROLE_ADMIN';
        $user->setRoles($roles);
        $this->entityManager->flush();

        $io->success(sprintf('ROLE_ADMIN granted to "%s".', $user->getEmail()));

        return Command::SUCCESS;
    }
}

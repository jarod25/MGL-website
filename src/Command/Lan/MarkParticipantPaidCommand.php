<?php

namespace App\Command\Lan;

use App\Entity\Participant;
use App\Entity\Subscription;
use App\Enum\RegistrationStatusEnum;
use App\Repository\ParticipantRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:lan:mark-participant-paid',
    description: 'Marks a LAN participant as paid with a subscription for local/dev manual testing.',
)]
class MarkParticipantPaidCommand extends Command
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('participantIdentifier', InputArgument::REQUIRED, 'Participant email or internal reference')
            ->addArgument('subscriptionSlug', InputArgument::REQUIRED, 'Existing subscription slug');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $participantIdentifier = trim((string) $input->getArgument('participantIdentifier'));
        $subscriptionSlug = trim((string) $input->getArgument('subscriptionSlug'));

        $participant = $this->participantRepository->findOneByEmailOrInternalReference($participantIdentifier);
        if (!$participant instanceof Participant) {
            $io->error(sprintf('No participant found for email or internal reference "%s".', $participantIdentifier));

            return Command::FAILURE;
        }

        $subscription = $this->subscriptionRepository->findOneBySlug($subscriptionSlug);
        if (!$subscription instanceof Subscription) {
            $io->error(sprintf('No subscription found with slug "%s".', $subscriptionSlug));

            return Command::FAILURE;
        }

        $participant
            ->setRegistrationStatus(RegistrationStatusEnum::PAID)
            ->setSubscription($subscription)
            ->setPaidAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $io->success(sprintf(
            'Participant %s (%s) marked as paid with subscription %s.',
            $participant->getEmail(),
            $participant->getInternalReference(),
            $subscription->getSlug(),
        ));

        return Command::SUCCESS;
    }
}

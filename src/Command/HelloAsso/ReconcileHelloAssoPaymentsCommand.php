<?php

namespace App\Command\HelloAsso;

use App\Enum\HelloAssoPaymentMatchStatusEnum;
use App\Repository\HelloAssoPaymentRepository;
use App\Service\HelloAsso\HelloAssoPaymentProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:helloasso:reconcile-payments',
    description: 'Retry unresolved HelloAsso payment reconciliation.',
)]
final class ReconcileHelloAssoPaymentsCommand extends Command
{
    public function __construct(
        private HelloAssoPaymentRepository $repo,
        private HelloAssoPaymentProcessor $processor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('paymentId', InputArgument::OPTIONAL, 'Specific HelloAsso payment id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $rows = $this->repo->findUnresolved($input->getArgument('paymentId'));
        $matched = 0;
        $unresolved = 0;
        $conflict = 0;

        foreach ($rows as $payment) {
            $this->processor->reconcile($payment);

            match ($payment->getMatchStatus()) {
                HelloAssoPaymentMatchStatusEnum::MATCHED => $matched++,
                HelloAssoPaymentMatchStatusEnum::PAYMENT_CONFLICT => $conflict++,
                default => $unresolved++,
            };
        }

        $io->success(sprintf(
            'Analysed: %d; matched: %d; unresolved: %d; conflicts: %d',
            count($rows),
            $matched,
            $unresolved,
            $conflict,
        ));

        return Command::SUCCESS;
    }
}

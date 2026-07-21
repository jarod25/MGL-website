<?php

namespace App\Service\HelloAsso;

use App\Entity\HelloAssoPayment;
use App\Entity\Participant;
use App\Entity\Subscription;
use App\Enum\HelloAssoPaymentMatchStatusEnum as MatchStatus;
use App\Enum\RegistrationStatusEnum;
use App\Repository\HelloAssoPaymentRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class HelloAssoPaymentProcessor
{
    public function __construct(
        private EntityManagerInterface $em,
        private HelloAssoPaymentRepository $payments,
        private ParticipantRepository $participants,
        private SubscriptionRepository $subscriptions,
        private ParticipantIdentityMatcher $identity,
        private string $org,
        private string $formType,
        private string $formSlug,
        private LoggerInterface $logger,
    ) {
    }

    public function process(HelloAssoWebhookPayload $payload): HelloAssoPayment
    {
        return $this->em->wrapInTransaction(fn (): HelloAssoPayment => $this->doProcess($payload));
    }

    public function reconcile(HelloAssoPayment $payment): HelloAssoPayment
    {
        $payload = new HelloAssoWebhookPayload(
            eventType: 'Payment',
            paymentId: $payment->getPaymentId() ?? '',
            paymentState: $payment->getPaymentState(),
            orderId: $payment->getOrderId(),
            organizationSlug: $payment->getOrganizationSlug(),
            formType: $payment->getFormType(),
            formSlug: $payment->getFormSlug(),
            tierId: $payment->getTierId(),
            amount: $payment->getAmount(),
            tierAmount: null,
            receiptUrl: $payment->getReceiptUrl(),
            payerEmail: $payment->getPayerEmail(),
            payerFirstname: $payment->getPayerFirstname(),
            payerLastname: $payment->getPayerLastname(),
            metadata: [],
            customFieldValues: [],
            payloadHash: $payment->getPayloadHash() ?? '',
        );

        return $this->em->wrapInTransaction(fn (): HelloAssoPayment => $this->evaluate($payment, $payload));
    }

    private function doProcess(HelloAssoWebhookPayload $payload): HelloAssoPayment
    {
        $payment = $this->findExistingPayment($payload) ?? (new HelloAssoPayment())->setPaymentId($payload->paymentId);
        $samePayload = $payment->getPayloadHash() === $payload->payloadHash;

        $this->fill($payment, $payload);

        if (!$payload->isExpected($this->org, $this->formType, $this->formSlug)) {
            $payment
                ->setMatchStatus(MatchStatus::IGNORED)
                ->setMismatchReason('Notification ignored: unexpected HelloAsso form.');

            $this->em->persist($payment);
            $this->em->flush();

            return $payment;
        }

        if ($samePayload && $payment->getMatchStatus() === MatchStatus::MATCHED) {
            return $payment;
        }

        try {
            $this->em->persist($payment);
            $this->evaluate($payment, $payload);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $this->em->clear();

            $existing = $this->payments->findOneBy(['paymentId' => $payload->paymentId]);

            if ($existing instanceof HelloAssoPayment) {
                return $existing;
            }

            throw $exception;
        }

        return $payment;
    }

    private function findExistingPayment(HelloAssoWebhookPayload $payload): ?HelloAssoPayment
    {
        $payment = $this->payments->findOneBy(['paymentId' => $payload->paymentId]);

        if ($payment instanceof HelloAssoPayment) {
            return $payment;
        }

        if ($payload->orderId === null) {
            return null;
        }

        $payment = $this->payments->findOneBy(['orderId' => $payload->orderId]);

        return $payment instanceof HelloAssoPayment ? $payment : null;
    }

    private function evaluate(HelloAssoPayment $payment, HelloAssoWebhookPayload $payload): HelloAssoPayment
    {
        $payment->setUpdatedAt(new \DateTimeImmutable());

        if ($payload->registrationCount > 1) {
            return $payment
                ->setMatchStatus(MatchStatus::MULTIPLE_REGISTRATIONS)
                ->setMismatchReason('HelloAsso order contains multiple Registration items; manual review required.');
        }

        if ($this->isBlocked($payment)) {
            return $payment;
        }

        if ($this->isRefunded($payload->paymentState)) {
            $payment
                ->setMatchStatus(MatchStatus::REFUNDED)
                ->setProcessedAt(new \DateTimeImmutable());

            $participant = $payment->getParticipant();

            if (
                $participant instanceof Participant
                && $participant->getRegistrationStatus() === RegistrationStatusEnum::PAID
            ) {
                $participant->setRegistrationStatus(RegistrationStatusEnum::REFUNDED);
            }

            return $payment;
        }

        if (!$this->isSuccessful($payload->paymentState)) {
            return $payment
                ->setMatchStatus(MatchStatus::PENDING)
                ->setMismatchReason('Payment state is not accepted for validation.');
        }

        $email = $payload->hasParticipantIdentity()
            ? $payload->effectiveEmail()
            : ($payment->getPayerEmail() ?? $payload->effectiveEmail());
        $firstname = $payload->hasParticipantIdentity()
            ? $payload->effectiveFirstname()
            : ($payment->getPayerFirstname() ?? $payload->effectiveFirstname());
        $lastname = $payload->hasParticipantIdentity()
            ? $payload->effectiveLastname()
            : ($payment->getPayerLastname() ?? $payload->effectiveLastname());
        $participant = $payment->getParticipant() ?? $this->findParticipant($payload, $email);

        if (!$participant instanceof Participant) {
            return $payment
                ->setMatchStatus(MatchStatus::UNMATCHED_PARTICIPANT)
                ->setMismatchReason('No participant found from internal reference or payer email.');
        }

        $payment->setParticipant($participant);

        if (!$this->emailMatches($participant, $email)) {
            return $payment
                ->setMatchStatus(MatchStatus::IDENTITY_MISMATCH)
                ->setMismatchReason('HelloAsso email does not match participant email.');
        }

        if (!$this->identity->matches($participant, $firstname, $lastname)) {
            return $payment
                ->setMatchStatus(MatchStatus::IDENTITY_MISMATCH)
                ->setMismatchReason('Payer firstname/lastname does not match participant identity.');
        }

        $subscription = $this->findActiveSubscription($payload->tierId);

        if (!$subscription instanceof Subscription) {
            return $payment
                ->setMatchStatus(MatchStatus::UNKNOWN_TIER)
                ->setMismatchReason('No active subscription matches the HelloAsso tierId.');
        }

        $payment->setSubscription($subscription);

        if ($payload->tierAmount !== null && $subscription->getPrice() !== $payload->tierAmount) {
            return $payment
                ->setMatchStatus(MatchStatus::AMOUNT_MISMATCH)
                ->setMismatchReason('HelloAsso item amount does not match subscription price.');
        }

        if ($participant->getRegistrationStatus() === RegistrationStatusEnum::PAID) {
            $otherPayment = $this->payments->createQueryBuilder('h')
                ->andWhere('h.participant = :participant')
                ->andWhere('h.matchStatus = :status')
                ->andWhere('h.paymentId != :paymentId')
                ->setParameter('participant', $participant)
                ->setParameter('status', MatchStatus::MATCHED)
                ->setParameter('paymentId', $payment->getPaymentId())
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($otherPayment instanceof HelloAssoPayment) {
                return $payment
                    ->setMatchStatus(MatchStatus::PAYMENT_CONFLICT)
                    ->setMismatchReason('Participant is already paid with another HelloAsso payment.');
            }
        } else {
            $participant
                ->setRegistrationStatus(RegistrationStatusEnum::PAID)
                ->setSubscription($subscription);

            if ($participant->getPaidAt() === null) {
                $participant->setPaidAt(new \DateTimeImmutable());
            }
        }

        $payment
            ->setMatchStatus(MatchStatus::MATCHED)
            ->setMismatchReason(null)
            ->setProcessedAt(new \DateTimeImmutable());

        return $payment;
    }

    private function fill(HelloAssoPayment $payment, HelloAssoWebhookPayload $payload): void
    {
        if ($payload->paymentId !== '' && $payment->getPaymentId() !== $payload->paymentId) {
            $payment->setPaymentId($payload->paymentId);
        }

        $payment
            ->setOrderId($payload->orderId ?? $payment->getOrderId())
            ->setOrganizationSlug($payload->organizationSlug ?? $payment->getOrganizationSlug())
            ->setFormType($payload->formType ?? $payment->getFormType())
            ->setFormSlug($payload->formSlug ?? $payment->getFormSlug())
            ->setTierId($payload->tierId ?? $payment->getTierId());

        if ($payload->hasParticipantIdentity() || $payment->getPayerEmail() === null) {
            $payment
                ->setPayerEmail($payload->effectiveEmail() ?? $payment->getPayerEmail())
                ->setPayerFirstname($payload->effectiveFirstname() ?? $payment->getPayerFirstname())
                ->setPayerLastname($payload->effectiveLastname() ?? $payment->getPayerLastname());
        }

        $payment
            ->setPaymentState($payload->paymentState ?? $payment->getPaymentState())
            ->setAmount($payload->amount ?? $payment->getAmount())
            ->setReceiptUrl($payload->receiptUrl ?? $payment->getReceiptUrl())
            ->setPayloadHash($payload->payloadHash);
    }

    private function findParticipant(HelloAssoWebhookPayload $payload, ?string $email = null): ?Participant
    {
        foreach ($payload->metadata as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $participant = $this->participants->findOneBy(['internalReference' => (string) $value]);

            if ($participant instanceof Participant) {
                return $participant;
            }
        }

        foreach ($payload->customFieldValues as $value) {
            $participant = $this->participants->findOneBy(['internalReference' => $value]);

            if ($participant instanceof Participant) {
                return $participant;
            }
        }

        foreach ($payload->customFieldValues as $value) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $participant = $this->participants->findOneByEmail($value);

            if ($participant instanceof Participant) {
                return $participant;
            }
        }

        return $email !== null ? $this->participants->findOneByEmail($email) : null;
    }

    private function emailMatches(Participant $participant, ?string $email): bool
    {
        return $email !== null
            && mb_strtolower(trim($participant->getEmail() ?? '')) === mb_strtolower(trim($email));
    }

    private function isBlocked(HelloAssoPayment $payment): bool
    {
        return in_array(
            $payment->getMatchStatus(),
            [
                MatchStatus::MULTIPLE_REGISTRATIONS,
                MatchStatus::IDENTITY_MISMATCH,
                MatchStatus::PAYMENT_CONFLICT,
            ],
            true,
        );
    }

    private function findActiveSubscription(?string $tierId): ?Subscription
    {
        if ($tierId === null) {
            return null;
        }

        $subscription = $this->subscriptions->findOneByHelloAssoTierId($tierId);

        return $subscription instanceof Subscription && $subscription->isActive() ? $subscription : null;
    }

    private function isSuccessful(?string $state): bool
    {
        return in_array(strtolower((string) $state), ['authorized', 'processed', 'succeeded', 'paid'], true);
    }

    private function isRefunded(?string $state): bool
    {
        return in_array(strtolower((string) $state), ['refunded', 'refund', 'reimbursed'], true);
    }
}

<?php

namespace App\Service\HelloAsso;

final readonly class HelloAssoWebhookPayload
{
    /**
     * @param array<string, mixed> $metadata
     * @param list<string> $customFieldValues
     * @param list<string> $paymentIds
     */
    public function __construct(
        public string $eventType,
        public string $paymentId,
        public ?string $paymentState,
        public ?string $orderId,
        public ?string $organizationSlug,
        public ?string $formType,
        public ?string $formSlug,
        public ?string $tierId,
        public ?int $amount,
        public ?int $tierAmount,
        public ?string $receiptUrl,
        public ?string $payerEmail,
        public ?string $payerFirstname,
        public ?string $payerLastname,
        public array $metadata,
        public array $customFieldValues,
        public string $payloadHash,
        public int $registrationCount = 0,
        public ?string $participantEmail = null,
        public ?string $participantFirstname = null,
        public ?string $participantLastname = null,
        public array $paymentIds = [],
    ) {
    }

    public function isExpected(string $organizationSlug, string $formType, string $formSlug): bool
    {
        return in_array(strtolower($this->eventType), ['payment', 'order'], true)
            && $this->organizationSlug === $organizationSlug
            && strcasecmp((string) $this->formType, $formType) === 0
            && $this->formSlug === $formSlug;
    }

    public function effectiveEmail(): ?string
    {
        return $this->participantEmail ?? $this->payerEmail;
    }

    public function effectiveFirstname(): ?string
    {
        return $this->participantFirstname ?? $this->payerFirstname;
    }

    public function effectiveLastname(): ?string
    {
        return $this->participantLastname ?? $this->payerLastname;
    }

    public function hasParticipantIdentity(): bool
    {
        return $this->participantEmail !== null
            || $this->participantFirstname !== null
            || $this->participantLastname !== null;
    }
}

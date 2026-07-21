<?php

namespace App\Tests;

use App\Service\HelloAsso\HelloAssoWebhookParser;
use App\Service\HelloAsso\HelloAssoWebhookPayload;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class HelloAssoParserTest extends TestCase
{
    public function testRejectsInvalidJson(): void
    {
        $this->expectException(BadRequestHttpException::class);

        (new HelloAssoWebhookParser())->parse('{');
    }

    public function testRejectsMissingEventType(): void
    {
        $this->expectException(BadRequestHttpException::class);

        (new HelloAssoWebhookParser())->parse('{"data":{"id":"RECETTE_CODEX"}}');
    }

    public function testFormEventCanBeParsedAsIgnoredCandidate(): void
    {
        $payload = $this->parse('ignored_form_event.json');

        self::assertSame('Form', $payload->eventType);
    }

    public function testPaymentExtractsPaymentDataWithoutTier(): void
    {
        $payload = $this->parse('payment_authorized.json');

        self::assertSame('RECETTE_CODEX_PAY_001', $payload->paymentId);
        self::assertSame('Authorized', $payload->paymentState);
        self::assertSame('RECETTE_CODEX_ORDER_001', $payload->orderId);
        self::assertSame('recette_codex_payer@example.test', $payload->payerEmail);
        self::assertSame(2500, $payload->amount);
        self::assertNull($payload->tierId);
        self::assertNull($payload->tierAmount);
    }

    public function testOrderExtractsRegistrationData(): void
    {
        $payload = $this->parse('order_single_registration.json');

        self::assertSame('RECETTE_CODEX_TIER_001', $payload->tierId);
        self::assertSame('recette_codex_player@example.test', $payload->participantEmail);
        self::assertContains('RECETTE_CODEX_REF_001', $payload->customFieldValues);
        self::assertSame(['RECETTE_CODEX_PAY_001'], $payload->paymentIds);
    }

    public function testRootMetadataIsRead(): void
    {
        $payload = $this->parse('order_root_metadata.json');

        self::assertSame('RECETTE_CODEX_ROOT_REF', $payload->metadata['internalReference']);
    }

    public function testAmountObjectIsRead(): void
    {
        $payload = $this->parse('order_amount_object.json');

        self::assertSame(3000, $payload->amount);
        self::assertSame(2500, $payload->tierAmount);
    }

    public function testMultipleRegistrationsAreCounted(): void
    {
        $payload = $this->parse('order_multiple_registrations.json');

        self::assertSame(2, $payload->registrationCount);
    }

    private function parse(string $fixture): HelloAssoWebhookPayload
    {
        return (new HelloAssoWebhookParser())->parse(file_get_contents(__DIR__.'/Fixtures/HelloAsso/'.$fixture));
    }
}

<?php
namespace App\Tests;
use App\Service\HelloAsso\HelloAssoWebhookParser;use PHPUnit\Framework\TestCase;
final class HelloAssoParserTest extends TestCase{public function testParsesFixture():void{$p=(new HelloAssoWebhookParser())->parse(file_get_contents(__DIR__.'/Fixtures/HelloAsso/payment_authorized.json'));self::assertSame('Payment',$p->eventType);self::assertSame('RECETTE_CODEX_PAY_001',$p->paymentId);self::assertSame('RECETTE_CODEX_TIER_001',$p->tierId);self::assertSame(2500,$p->tierAmount);self::assertContains('RECETTE_CODEX_REF_001',$p->customFieldValues);}public function testRejectsInvalidJson():void{$this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);(new HelloAssoWebhookParser())->parse('{');}}

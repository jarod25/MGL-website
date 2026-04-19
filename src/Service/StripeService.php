<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient($_ENV['STRIPE_SK']);
    }

    public function createCheckoutSession($event, $successUrl, $cancelUrl): Session
    {
        $priceInCents = (int) round($event->getPrice() * 100);

        return $this->stripe->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $event->getTitle(),
                    ],
                    'unit_amount' => $priceInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    public function getWebhooks(Request $request): array
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $_ENV['STRIPE_WEBHOOK_SECRET']
            );
        } catch (SignatureVerificationException | \UnexpectedValueException $e) {
            return ['error' => $e->getMessage()];
        }

        return match ($event->type) {
            'payment_intent.succeeded' => ['status' => 'success'],
            'payment_intent.failed' => ['status' => 'fail'],
            'payment_intent.processing' => ['status' => 'processing'],
            default => ['status' => 'unknown'],
        };
    }

}

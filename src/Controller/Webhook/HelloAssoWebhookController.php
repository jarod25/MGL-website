<?php

namespace App\Controller\Webhook;

use App\Service\HelloAsso\HelloAssoPaymentProcessor;
use App\Service\HelloAsso\HelloAssoWebhookAuthenticator;
use App\Service\HelloAsso\HelloAssoWebhookParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HelloAssoWebhookController extends AbstractController
{
    #[Route('/webhooks/helloasso', name: 'app_helloasso_webhook', methods: ['POST'])]
    public function __invoke(
        Request $request,
        HelloAssoWebhookAuthenticator $auth,
        HelloAssoWebhookParser $parser,
        HelloAssoPaymentProcessor $processor,
    ): JsonResponse {
        $raw = $request->getContent();

        if (!$auth->isAuthenticated($request, $raw)) {
            return new JsonResponse(['status' => 'forbidden'], 403);
        }

        $payload = $parser->parse($raw);
        $payment = $processor->process($payload);

        return new JsonResponse([
            'status' => 'ok',
            'matchStatus' => $payment->getMatchStatus()->value,
        ]);
    }
}

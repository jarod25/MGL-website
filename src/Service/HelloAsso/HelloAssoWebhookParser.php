<?php

namespace App\Service\HelloAsso;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class HelloAssoWebhookParser
{
    public function parse(string $raw): HelloAssoWebhookPayload
    {
        $payload = json_decode($raw, true);

        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        $eventType = $this->string($payload['eventType'] ?? null);

        if ($eventType === null) {
            throw new BadRequestHttpException('Missing eventType.');
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        if ($data === []) {
            throw new BadRequestHttpException('Missing data.');
        }

        $order = is_array($data['order'] ?? null) ? $data['order'] : $data;
        $payer = is_array($data['payer'] ?? null) ? $data['payer'] : [];
        $items = $this->items($data, $order);
        $paymentIds = $this->paymentIds($data, $order);
        $paymentId = $this->string($data['id'] ?? $data['paymentId'] ?? null) ?? ($paymentIds[0] ?? null);

        if (strcasecmp($eventType, 'Payment') === 0 && $paymentId === null) {
            throw new BadRequestHttpException('Missing payment id.');
        }

        $paymentState = $this->string($data['state'] ?? $data['paymentState'] ?? null);

        if (strcasecmp($eventType, 'Payment') === 0 && $paymentState === null) {
            throw new BadRequestHttpException('Missing payment state.');
        }

        $registrationData = $this->registrationData($items);
        $metadata = $this->mergeMetadata(
            $payload['metadata'] ?? null,
            $data['metadata'] ?? null,
            $order['metadata'] ?? null,
        );

        return new HelloAssoWebhookPayload(
            eventType: $eventType,
            paymentId: $paymentId ?? ($this->string($order['id'] ?? null) ?? ''),
            paymentState: $paymentState,
            orderId: $this->string($order['id'] ?? $data['orderId'] ?? null),
            organizationSlug: $this->string($order['organizationSlug'] ?? $data['organizationSlug'] ?? null),
            formType: $this->string($order['formType'] ?? $data['formType'] ?? null),
            formSlug: $this->string($order['formSlug'] ?? $data['formSlug'] ?? null),
            tierId: $registrationData['tierId'],
            amount: $this->parseAmount($data['amount'] ?? $data['totalAmount'] ?? $order['amount'] ?? $order['totalAmount'] ?? null),
            tierAmount: $registrationData['tierAmount'],
            receiptUrl: $this->string($data['paymentReceiptUrl'] ?? $data['receiptUrl'] ?? null),
            payerEmail: $this->string($payer['email'] ?? null),
            payerFirstname: $this->string($payer['firstName'] ?? $payer['firstname'] ?? null),
            payerLastname: $this->string($payer['lastName'] ?? $payer['lastname'] ?? null),
            metadata: $metadata,
            customFieldValues: $registrationData['customFieldValues'],
            payloadHash: hash('sha256', $raw),
            registrationCount: $registrationData['registrationCount'],
            participantEmail: $registrationData['participantEmail'],
            participantFirstname: $registrationData['participantFirstname'],
            participantLastname: $registrationData['participantLastname'],
            paymentIds: $paymentIds,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $order
     *
     * @return list<array<string, mixed>>
     */
    private function items(array $data, array $order): array
    {
        if (is_array($data['items'] ?? null)) {
            return $data['items'];
        }

        if (is_array($order['items'] ?? null)) {
            return $order['items'];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $order
     *
     * @return list<string>
     */
    private function paymentIds(array $data, array $order): array
    {
        $payments = is_array($data['payments'] ?? null)
            ? $data['payments']
            : (is_array($order['payments'] ?? null) ? $order['payments'] : []);
        $paymentIds = [];

        foreach ($payments as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $paymentId = $this->string($payment['id'] ?? $payment['paymentId'] ?? null);

            if ($paymentId !== null) {
                $paymentIds[] = $paymentId;
            }
        }

        return $paymentIds;
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return array{
     *     tierId: ?string,
     *     tierAmount: ?int,
     *     customFieldValues: list<string>,
     *     registrationCount: int,
     *     participantEmail: ?string,
     *     participantFirstname: ?string,
     *     participantLastname: ?string
     * }
     */
    private function registrationData(array $items): array
    {
        $tierId = null;
        $tierAmount = null;
        $customFieldValues = [];
        $registrationCount = 0;
        $participantEmail = null;
        $participantFirstname = null;
        $participantLastname = null;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $type = $this->string($item['type'] ?? $item['itemType'] ?? null);
            $isRegistration = $type === null || strcasecmp($type, 'Registration') === 0;

            if (!$isRegistration) {
                continue;
            }

            ++$registrationCount;

            $itemTierId = $this->string($item['tierId'] ?? $item['tier'] ?? null);

            if ($tierId === null && $itemTierId !== null) {
                $tierId = $itemTierId;
            }

            $itemAmount = $this->parseAmount($item['amount'] ?? $item['price'] ?? $item['payments'][0]['amount'] ?? null);

            if ($tierAmount === null && $itemTierId !== null && $itemAmount !== null) {
                $tierAmount = $itemAmount;
            }

            if (is_array($item['user'] ?? null)) {
                $user = $item['user'];
                $participantEmail = $this->string($user['email'] ?? null) ?? $participantEmail;
                $participantFirstname = $this->string($user['firstName'] ?? $user['firstname'] ?? null) ?? $participantFirstname;
                $participantLastname = $this->string($user['lastName'] ?? $user['lastname'] ?? null) ?? $participantLastname;
            }

            foreach (($item['customFields'] ?? $item['customfields'] ?? []) as $customField) {
                if (!is_array($customField)) {
                    continue;
                }

                $value = $this->string($customField['answer'] ?? $customField['value'] ?? $customField['response'] ?? null);

                if ($value !== null) {
                    $customFieldValues[] = $value;
                }
            }
        }

        return [
            'tierId' => $tierId,
            'tierAmount' => $tierAmount,
            'customFieldValues' => $customFieldValues,
            'registrationCount' => $registrationCount,
            'participantEmail' => $participantEmail,
            'participantFirstname' => $participantFirstname,
            'participantLastname' => $participantLastname,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mergeMetadata(mixed ...$sources): array
    {
        $metadata = [];

        foreach ($sources as $source) {
            if (!is_array($source)) {
                continue;
            }

            foreach ($source as $key => $value) {
                if ($value !== null) {
                    $metadata[$key] = $value;
                }
            }
        }

        return $metadata;
    }

    private function string(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function parseAmount(mixed $value): ?int
    {
        if (is_array($value)) {
            $value = $value['total'] ?? $value['amount'] ?? null;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}

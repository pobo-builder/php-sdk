<?php

declare(strict_types=1);

namespace Pobo\Sdk;

use Pobo\Sdk\DTO\WebhookPayload;
use Pobo\Sdk\Enum\WebhookEvent;
use Pobo\Sdk\Exception\WebhookException;

class WebhookHandler
{
    public function __construct(
        private readonly string $webhookSecret,
    ) {
    }

    /**
     * Handle incoming webhook request
     *
     * @throws WebhookException
     */
    public function handle(string $payload, string $signature): WebhookPayload
    {
        if ($signature === '') {
            throw WebhookException::missingSignature();
        }

        if ($this->verifySignature($payload, $signature) === false) {
            throw WebhookException::invalidSignature();
        }

        return $this->parsePayload($payload);
    }

    /**
     * Handle webhook from global PHP variables
     *
     * @throws WebhookException
     */
    public function handleFromGlobals(): WebhookPayload
    {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

        if ($payload === false) {
            throw WebhookException::invalidPayload();
        }

        return $this->handle($payload, $signature);
    }

    /**
     * Verify webhook signature using HMAC-SHA256
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        $calculatedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Parse and validate webhook payload
     *
     * @throws WebhookException
     */
    private function parsePayload(string $payload): WebhookPayload
    {
        $data = json_decode($payload, true);

        if ($data === null || is_array($data) === false) {
            throw WebhookException::invalidPayload();
        }

        $eventString = $data['event'] ?? '';
        $event = WebhookEvent::fromString($eventString);

        if ($event === null) {
            throw WebhookException::unknownEvent($eventString);
        }

        return WebhookPayload::fromArray($data, $event);
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\WebhookEvent;

readonly class WebhookPayload
{
    public function __construct(
        public WebhookEvent $event,
        public \DateTimeInterface $timestamp,
        public int $eshopId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, WebhookEvent $event): self
    {
        return new self(
            event: $event,
            timestamp: new \DateTimeImmutable($data['timestamp']),
            eshopId: $data['eshop_id'],
        );
    }
}

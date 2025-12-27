<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\WebhookEvent;

final class WebhookPayload
{
    public function __construct(
        public readonly WebhookEvent $event,
        public readonly \DateTimeInterface $timestamp,
        public readonly int $eshopId,
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

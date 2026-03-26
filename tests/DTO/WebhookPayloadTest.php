<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\WebhookPayload;
use Pobo\Sdk\Enum\WebhookEvent;

final class WebhookPayloadTest extends TestCase
{
    public function testFromArrayWithProductsUpdate(): void
    {
        $data = [
            'timestamp' => '2024-01-15T10:30:00.000000Z',
            'eshop_id' => 42,
        ];

        $payload = WebhookPayload::fromArray($data, WebhookEvent::PRODUCTS_UPDATE);

        $this->assertSame(WebhookEvent::PRODUCTS_UPDATE, $payload->event);
        $this->assertSame(42, $payload->eshopId);
        $this->assertInstanceOf(\DateTimeInterface::class, $payload->timestamp);
    }

    public function testFromArrayWithCategoriesUpdate(): void
    {
        $data = [
            'timestamp' => '2024-06-01T08:00:00Z',
            'eshop_id' => 1,
        ];

        $payload = WebhookPayload::fromArray($data, WebhookEvent::CATEGORIES_UPDATE);

        $this->assertSame(WebhookEvent::CATEGORIES_UPDATE, $payload->event);
        $this->assertSame(1, $payload->eshopId);
    }

    public function testFromArrayWithBlogsUpdate(): void
    {
        $data = [
            'timestamp' => '2024-12-25T00:00:00Z',
            'eshop_id' => 999,
        ];

        $payload = WebhookPayload::fromArray($data, WebhookEvent::BLOGS_UPDATE);

        $this->assertSame(WebhookEvent::BLOGS_UPDATE, $payload->event);
        $this->assertSame(999, $payload->eshopId);
    }

    public function testTimestampIsParsedCorrectly(): void
    {
        $data = [
            'timestamp' => '2024-03-15T14:30:00.000000Z',
            'eshop_id' => 5,
        ];

        $payload = WebhookPayload::fromArray($data, WebhookEvent::PRODUCTS_UPDATE);

        $this->assertSame('2024-03-15', $payload->timestamp->format('Y-m-d'));
        $this->assertSame('14:30:00', $payload->timestamp->format('H:i:s'));
    }
}

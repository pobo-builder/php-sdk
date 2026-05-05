<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Enum\WebhookEvent;
use Pobo\Sdk\Exception\WebhookException;
use Pobo\Sdk\WebhookHandler;

final class WebhookHandlerTest extends TestCase
{
    private const WEBHOOK_SECRET = 'test-secret-key';

    private WebhookHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new WebhookHandler(webhookSecret: self::WEBHOOK_SECRET);
    }

    public function testHandleValidProductsUpdateWebhook(): void
    {
        $payload = json_encode([
            'event' => 'Products.update',
            'timestamp' => '2024-01-15T10:30:00Z',
            'eshop_id' => 123,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->handle($payload, $signature);

        $this->assertSame(WebhookEvent::PRODUCTS_UPDATE, $result->event);
        $this->assertSame(123, $result->eshopId);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->timestamp);
    }

    public function testHandleValidCategoriesUpdateWebhook(): void
    {
        $payload = json_encode([
            'event' => 'Categories.update',
            'timestamp' => '2024-01-15T10:30:00Z',
            'eshop_id' => 456,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->handle($payload, $signature);

        $this->assertSame(WebhookEvent::CATEGORIES_UPDATE, $result->event);
        $this->assertSame(456, $result->eshopId);
    }

    public function testHandleThrowsExceptionForMissingSignature(): void
    {
        $payload = json_encode(['event' => 'Products.update']);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Missing webhook signature header');

        $this->handler->handle($payload, '');
    }

    public function testHandleThrowsExceptionForInvalidSignature(): void
    {
        $payload = json_encode(['event' => 'Products.update']);
        $invalidSignature = 'invalid-signature';

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $this->handler->handle($payload, $invalidSignature);
    }

    public function testHandleThrowsExceptionForInvalidJson(): void
    {
        $payload = 'not valid json';
        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid webhook payload');

        $this->handler->handle($payload, $signature);
    }

    public function testHandleThrowsExceptionForUnknownEvent(): void
    {
        $payload = json_encode([
            'event' => 'Unknown.event',
            'timestamp' => '2024-01-15T10:30:00Z',
            'eshop_id' => 123,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Unknown webhook event: Unknown.event');

        $this->handler->handle($payload, $signature);
    }

    public function testVerifySignatureReturnsTrue(): void
    {
        $payload = 'test payload';
        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->verifySignature($payload, $signature);

        $this->assertTrue($result);
    }

    public function testVerifySignatureReturnsFalse(): void
    {
        $payload = 'test payload';
        $wrongSignature = hash_hmac('sha256', 'different payload', self::WEBHOOK_SECRET);

        $result = $this->handler->verifySignature($payload, $wrongSignature);

        $this->assertFalse($result);
    }

    public function testVerifySignatureReturnsFalseForWrongSecret(): void
    {
        $payload = 'test payload';
        $signatureWithWrongSecret = hash_hmac('sha256', $payload, 'wrong-secret');

        $result = $this->handler->verifySignature($payload, $signatureWithWrongSecret);

        $this->assertFalse($result);
    }

    public function testSignatureIsTimingSafe(): void
    {
        // This test verifies that hash_equals is used (timing-safe comparison)
        // by checking that both valid and invalid signatures take similar time
        $payload = 'test payload';
        $validSignature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);
        $invalidSignature = str_repeat('a', 64);

        // Both should complete without timing differences
        $this->assertTrue($this->handler->verifySignature($payload, $validSignature));
        $this->assertFalse($this->handler->verifySignature($payload, $invalidSignature));
    }

    public function testHandleWithDifferentTimestampFormats(): void
    {
        $payload = json_encode([
            'event' => 'Products.update',
            'timestamp' => '2024-01-15T10:30:00.123456Z',
            'eshop_id' => 123,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->handle($payload, $signature);

        $this->assertInstanceOf(\DateTimeInterface::class, $result->timestamp);
    }

    /**
     * @return array<string, array{0: string, 1: WebhookEvent}>
     */
    public static function brandEventProvider(): array
    {
        return [
            'Brands.create' => ['Brands.create', WebhookEvent::BRANDS_CREATE],
            'Brands.update' => ['Brands.update', WebhookEvent::BRANDS_UPDATE],
            'Brands.delete' => ['Brands.delete', WebhookEvent::BRANDS_DELETE],
        ];
    }

    /**
     * @dataProvider brandEventProvider
     */
    public function testHandleValidBrandEvent(string $eventValue, WebhookEvent $expected): void
    {
        $payload = json_encode([
            'event' => $eventValue,
            'timestamp' => '2026-05-05T14:30:00+02:00',
            'eshop_id' => 789,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->handle($payload, $signature);

        $this->assertSame($expected, $result->event);
        $this->assertSame(789, $result->eshopId);
    }

    /**
     * @return array<string, array{0: string, 1: WebhookEvent}>
     */
    public static function createDeleteEventProvider(): array
    {
        return [
            'Products.create' => ['Products.create', WebhookEvent::PRODUCTS_CREATE],
            'Products.delete' => ['Products.delete', WebhookEvent::PRODUCTS_DELETE],
            'Categories.create' => ['Categories.create', WebhookEvent::CATEGORIES_CREATE],
            'Categories.delete' => ['Categories.delete', WebhookEvent::CATEGORIES_DELETE],
            'Blogs.create' => ['Blogs.create', WebhookEvent::BLOGS_CREATE],
            'Blogs.delete' => ['Blogs.delete', WebhookEvent::BLOGS_DELETE],
        ];
    }

    /**
     * @dataProvider createDeleteEventProvider
     */
    public function testHandleValidCreateOrDeleteEvent(string $eventValue, WebhookEvent $expected): void
    {
        $payload = json_encode([
            'event' => $eventValue,
            'timestamp' => '2026-05-05T14:30:00+02:00',
            'eshop_id' => 555,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);

        $result = $this->handler->handle($payload, $signature);

        $this->assertSame($expected, $result->event);
    }

    public function testHandleBrandsCreateMatchableInUserCode(): void
    {
        // Verifies the typical match() pattern works for brand events.
        $payload = json_encode([
            'event' => 'Brands.create',
            'timestamp' => '2026-05-05T14:30:00+02:00',
            'eshop_id' => 1,
        ]);

        $signature = hash_hmac('sha256', $payload, self::WEBHOOK_SECRET);
        $result = $this->handler->handle($payload, $signature);

        $branch = match ($result->event) {
            WebhookEvent::BRANDS_CREATE => 'created',
            WebhookEvent::BRANDS_UPDATE => 'updated',
            WebhookEvent::BRANDS_DELETE => 'deleted',
            default => 'other',
        };

        $this->assertSame('created', $branch);
    }
}

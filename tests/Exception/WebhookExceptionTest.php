<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Exception\WebhookException;

final class WebhookExceptionTest extends TestCase
{
    public function testInvalidSignature(): void
    {
        $exception = WebhookException::invalidSignature();

        $this->assertSame('Invalid webhook signature', $exception->getMessage());
    }

    public function testInvalidPayload(): void
    {
        $exception = WebhookException::invalidPayload();

        $this->assertSame('Invalid webhook payload - could not parse JSON', $exception->getMessage());
    }

    public function testMissingSignature(): void
    {
        $exception = WebhookException::missingSignature();

        $this->assertSame('Missing webhook signature header', $exception->getMessage());
    }

    public function testUnknownEvent(): void
    {
        $exception = WebhookException::unknownEvent('Unknown.event');

        $this->assertSame('Unknown webhook event: Unknown.event', $exception->getMessage());
    }
}

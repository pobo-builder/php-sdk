<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Enum\WebhookEvent;

final class WebhookEventTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertSame('Products.update', WebhookEvent::PRODUCTS_UPDATE->value);
        $this->assertSame('Categories.update', WebhookEvent::CATEGORIES_UPDATE->value);
    }

    public function testValues(): void
    {
        $values = WebhookEvent::values();

        $this->assertContains('Products.update', $values);
        $this->assertContains('Categories.update', $values);
        $this->assertCount(2, $values);
    }

    public function testFromStringReturnsCorrectEnum(): void
    {
        $this->assertSame(WebhookEvent::PRODUCTS_UPDATE, WebhookEvent::fromString('Products.update'));
        $this->assertSame(WebhookEvent::CATEGORIES_UPDATE, WebhookEvent::fromString('Categories.update'));
    }

    public function testFromStringReturnsNullForUnknown(): void
    {
        $this->assertNull(WebhookEvent::fromString('Unknown.event'));
        $this->assertNull(WebhookEvent::fromString(''));
        $this->assertNull(WebhookEvent::fromString('products.update'));
        $this->assertNull(WebhookEvent::fromString('PRODUCTS.UPDATE'));
    }
}

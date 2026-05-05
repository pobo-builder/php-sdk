<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Enum\WebhookEvent;

final class WebhookEventTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertSame('Products.create', WebhookEvent::PRODUCTS_CREATE->value);
        $this->assertSame('Products.update', WebhookEvent::PRODUCTS_UPDATE->value);
        $this->assertSame('Products.delete', WebhookEvent::PRODUCTS_DELETE->value);

        $this->assertSame('Categories.create', WebhookEvent::CATEGORIES_CREATE->value);
        $this->assertSame('Categories.update', WebhookEvent::CATEGORIES_UPDATE->value);
        $this->assertSame('Categories.delete', WebhookEvent::CATEGORIES_DELETE->value);

        $this->assertSame('Brands.create', WebhookEvent::BRANDS_CREATE->value);
        $this->assertSame('Brands.update', WebhookEvent::BRANDS_UPDATE->value);
        $this->assertSame('Brands.delete', WebhookEvent::BRANDS_DELETE->value);

        $this->assertSame('Blogs.create', WebhookEvent::BLOGS_CREATE->value);
        $this->assertSame('Blogs.update', WebhookEvent::BLOGS_UPDATE->value);
        $this->assertSame('Blogs.delete', WebhookEvent::BLOGS_DELETE->value);
    }

    public function testValues(): void
    {
        $values = WebhookEvent::values();

        $this->assertCount(12, $values);
        $this->assertContains('Products.create', $values);
        $this->assertContains('Products.update', $values);
        $this->assertContains('Products.delete', $values);
        $this->assertContains('Categories.create', $values);
        $this->assertContains('Categories.update', $values);
        $this->assertContains('Categories.delete', $values);
        $this->assertContains('Brands.create', $values);
        $this->assertContains('Brands.update', $values);
        $this->assertContains('Brands.delete', $values);
        $this->assertContains('Blogs.create', $values);
        $this->assertContains('Blogs.update', $values);
        $this->assertContains('Blogs.delete', $values);
    }

    public function testFromStringReturnsCorrectEnumForExistingEvents(): void
    {
        $this->assertSame(WebhookEvent::PRODUCTS_UPDATE, WebhookEvent::fromString('Products.update'));
        $this->assertSame(WebhookEvent::CATEGORIES_UPDATE, WebhookEvent::fromString('Categories.update'));
        $this->assertSame(WebhookEvent::BLOGS_UPDATE, WebhookEvent::fromString('Blogs.update'));
    }

    public function testFromStringReturnsCorrectEnumForCreateEvents(): void
    {
        $this->assertSame(WebhookEvent::PRODUCTS_CREATE, WebhookEvent::fromString('Products.create'));
        $this->assertSame(WebhookEvent::CATEGORIES_CREATE, WebhookEvent::fromString('Categories.create'));
        $this->assertSame(WebhookEvent::BRANDS_CREATE, WebhookEvent::fromString('Brands.create'));
        $this->assertSame(WebhookEvent::BLOGS_CREATE, WebhookEvent::fromString('Blogs.create'));
    }

    public function testFromStringReturnsCorrectEnumForDeleteEvents(): void
    {
        $this->assertSame(WebhookEvent::PRODUCTS_DELETE, WebhookEvent::fromString('Products.delete'));
        $this->assertSame(WebhookEvent::CATEGORIES_DELETE, WebhookEvent::fromString('Categories.delete'));
        $this->assertSame(WebhookEvent::BRANDS_DELETE, WebhookEvent::fromString('Brands.delete'));
        $this->assertSame(WebhookEvent::BLOGS_DELETE, WebhookEvent::fromString('Blogs.delete'));
    }

    public function testFromStringReturnsCorrectEnumForBrandUpdate(): void
    {
        $this->assertSame(WebhookEvent::BRANDS_UPDATE, WebhookEvent::fromString('Brands.update'));
    }

    public function testFromStringReturnsNullForUnknown(): void
    {
        $this->assertNull(WebhookEvent::fromString('Unknown.event'));
        $this->assertNull(WebhookEvent::fromString(''));
        $this->assertNull(WebhookEvent::fromString('products.update'));
        $this->assertNull(WebhookEvent::fromString('PRODUCTS.UPDATE'));
        $this->assertNull(WebhookEvent::fromString('blogs.update'));
        $this->assertNull(WebhookEvent::fromString('BLOGS.UPDATE'));
        $this->assertNull(WebhookEvent::fromString('brands.create'));
        $this->assertNull(WebhookEvent::fromString('Languages.create')); // not in SDK yet
    }

    public function testIsCreateHelper(): void
    {
        $this->assertTrue(WebhookEvent::PRODUCTS_CREATE->isCreate());
        $this->assertTrue(WebhookEvent::CATEGORIES_CREATE->isCreate());
        $this->assertTrue(WebhookEvent::BRANDS_CREATE->isCreate());
        $this->assertTrue(WebhookEvent::BLOGS_CREATE->isCreate());

        $this->assertFalse(WebhookEvent::PRODUCTS_UPDATE->isCreate());
        $this->assertFalse(WebhookEvent::BRANDS_DELETE->isCreate());
    }

    public function testIsUpdateHelper(): void
    {
        $this->assertTrue(WebhookEvent::PRODUCTS_UPDATE->isUpdate());
        $this->assertTrue(WebhookEvent::CATEGORIES_UPDATE->isUpdate());
        $this->assertTrue(WebhookEvent::BRANDS_UPDATE->isUpdate());
        $this->assertTrue(WebhookEvent::BLOGS_UPDATE->isUpdate());

        $this->assertFalse(WebhookEvent::PRODUCTS_CREATE->isUpdate());
        $this->assertFalse(WebhookEvent::BRANDS_DELETE->isUpdate());
    }

    public function testIsDeleteHelper(): void
    {
        $this->assertTrue(WebhookEvent::PRODUCTS_DELETE->isDelete());
        $this->assertTrue(WebhookEvent::CATEGORIES_DELETE->isDelete());
        $this->assertTrue(WebhookEvent::BRANDS_DELETE->isDelete());
        $this->assertTrue(WebhookEvent::BLOGS_DELETE->isDelete());

        $this->assertFalse(WebhookEvent::PRODUCTS_CREATE->isDelete());
        $this->assertFalse(WebhookEvent::BRANDS_UPDATE->isDelete());
    }
}

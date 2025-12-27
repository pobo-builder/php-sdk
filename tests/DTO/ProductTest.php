<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;

final class ProductTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Test Product'),
            url: LocalizedString::create('https://example.com/product'),
        );

        $expected = [
            'id' => 'PROD-001',
            'is_visible' => true,
            'name' => ['default' => 'Test Product'],
            'url' => ['default' => 'https://example.com/product'],
        ];

        $this->assertSame($expected, $product->toArray());
    }

    public function testToArrayWithAllFields(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Test Product'),
            url: LocalizedString::create('https://example.com/product'),
            shortDescription: LocalizedString::create('Short desc'),
            description: LocalizedString::create('<p>Long desc</p>'),
            seoTitle: LocalizedString::create('SEO Title'),
            seoDescription: LocalizedString::create('SEO Description'),
            images: ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'],
            categoriesIds: ['CAT-001', 'CAT-002'],
            parametersIds: [1, 2, 3],
        );

        $array = $product->toArray();

        $this->assertSame('PROD-001', $array['id']);
        $this->assertTrue($array['is_visible']);
        $this->assertSame(['default' => 'Short desc'], $array['short_description']);
        $this->assertSame(['default' => '<p>Long desc</p>'], $array['description']);
        $this->assertSame(['default' => 'SEO Title'], $array['seo_title']);
        $this->assertSame(['default' => 'SEO Description'], $array['seo_description']);
        $this->assertSame(['https://example.com/img1.jpg', 'https://example.com/img2.jpg'], $array['images']);
        $this->assertSame(['CAT-001', 'CAT-002'], $array['categories_ids']);
        $this->assertSame([1, 2, 3], $array['parameters_ids']);
    }

    public function testToArrayExcludesEmptyArrays(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Test Product'),
            url: LocalizedString::create('https://example.com/product'),
            images: [],
            categoriesIds: [],
            parametersIds: [],
        );

        $array = $product->toArray();

        $this->assertArrayNotHasKey('images', $array);
        $this->assertArrayNotHasKey('categories_ids', $array);
        $this->assertArrayNotHasKey('parameters_ids', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'PROD-001',
            'is_visible' => true,
            'name' => ['default' => 'Test Product'],
            'url' => ['default' => 'https://example.com/product'],
            'short_description' => ['default' => 'Short desc'],
            'images' => ['https://example.com/img1.jpg'],
            'categories_ids' => ['CAT-001'],
            'parameters_ids' => [1, 2],
            'guid' => '550e8400-e29b-41d4-a716-446655440000',
            'is_loaded' => false,
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $product = Product::fromArray($data);

        $this->assertSame('PROD-001', $product->id);
        $this->assertTrue($product->isVisible);
        $this->assertSame('Test Product', $product->name->getDefault());
        $this->assertSame('https://example.com/product', $product->url->getDefault());
        $this->assertSame('Short desc', $product->shortDescription?->getDefault());
        $this->assertSame(['https://example.com/img1.jpg'], $product->images);
        $this->assertSame(['CAT-001'], $product->categoriesIds);
        $this->assertSame([1, 2], $product->parametersIds);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $product->guid);
        $this->assertFalse($product->isLoaded);
        $this->assertInstanceOf(\DateTimeInterface::class, $product->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $product->updatedAt);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 'PROD-001',
            'is_visible' => false,
            'name' => ['default' => 'Test'],
            'url' => ['default' => 'https://example.com'],
        ];

        $product = Product::fromArray($data);

        $this->assertSame('PROD-001', $product->id);
        $this->assertFalse($product->isVisible);
        $this->assertNull($product->shortDescription);
        $this->assertNull($product->description);
        $this->assertSame([], $product->images);
        $this->assertNull($product->guid);
        $this->assertNull($product->createdAt);
    }
}

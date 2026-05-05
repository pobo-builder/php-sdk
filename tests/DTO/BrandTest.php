<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\LocalizedString;

final class BrandTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple'),
            url: LocalizedString::create('https://example.com/znacky/apple'),
        );

        $expected = [
            'id' => 'BRAND-001',
            'is_visible' => true,
            'name' => ['default' => 'Apple'],
            'url' => ['default' => 'https://example.com/znacky/apple'],
        ];

        $this->assertSame($expected, $brand->toArray());
    }

    public function testToArrayWithAllFields(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple'),
            url: LocalizedString::create('https://example.com/znacky/apple'),
            imagePreview: 'https://example.com/brands/apple-logo.png',
            description: LocalizedString::create('<p>Apple Inc.</p>'),
            seoTitle: LocalizedString::create('Apple | Shop'),
            seoDescription: LocalizedString::create('Best Apple products'),
        );

        $array = $brand->toArray();

        $this->assertSame('https://example.com/brands/apple-logo.png', $array['image_preview']);
        $this->assertSame(['default' => '<p>Apple Inc.</p>'], $array['description']);
        $this->assertSame(['default' => 'Apple | Shop'], $array['seo_title']);
        $this->assertSame(['default' => 'Best Apple products'], $array['seo_description']);
    }

    public function testToArrayOmitsImagePreviewWhenNull(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple'),
            url: LocalizedString::create('https://example.com/znacky/apple'),
            imagePreview: null,
        );

        $array = $brand->toArray();

        $this->assertArrayNotHasKey('image_preview', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'BRAND-001',
            'is_visible' => true,
            'name' => ['default' => 'Apple', 'cs' => 'Apple CZ'],
            'url' => ['default' => 'https://example.com/znacky/apple'],
            'image_preview' => 'https://example.com/brands/apple-logo.png',
            'description' => ['default' => 'Description'],
            'guid' => '550e8400-e29b-41d4-a716-446655440099',
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $brand = Brand::fromArray($data);

        $this->assertSame('BRAND-001', $brand->id);
        $this->assertTrue($brand->isVisible);
        $this->assertSame('Apple', $brand->name->getDefault());
        $this->assertSame('https://example.com/brands/apple-logo.png', $brand->imagePreview);
        $this->assertSame('Description', $brand->description?->getDefault());
        $this->assertSame('550e8400-e29b-41d4-a716-446655440099', $brand->guid);
        $this->assertInstanceOf(\DateTimeInterface::class, $brand->createdAt);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 'BRAND-001',
            'is_visible' => false,
            'name' => ['default' => 'Test'],
            'url' => ['default' => 'https://example.com'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertSame('BRAND-001', $brand->id);
        $this->assertFalse($brand->isVisible);
        $this->assertNull($brand->imagePreview);
        $this->assertNull($brand->description);
        $this->assertNull($brand->guid);
    }

    public function testFromArrayWithExplicitNullImagePreview(): void
    {
        $data = [
            'id' => 'BRAND-001',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
            'image_preview' => null,
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->imagePreview);
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;

final class CategoryTest extends TestCase
{
    public function testToArrayWithRequiredFieldsOnly(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Electronics'),
            url: LocalizedString::create('https://example.com/electronics'),
        );

        $expected = [
            'id' => 'CAT-001',
            'is_visible' => true,
            'name' => ['default' => 'Electronics'],
            'url' => ['default' => 'https://example.com/electronics'],
        ];

        $this->assertSame($expected, $category->toArray());
    }

    public function testToArrayWithAllFields(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Electronics'),
            url: LocalizedString::create('https://example.com/electronics'),
            description: LocalizedString::create('<p>All electronics</p>'),
            seoTitle: LocalizedString::create('Electronics | Shop'),
            seoDescription: LocalizedString::create('Best electronics'),
            images: ['https://example.com/cat.jpg'],
        );

        $array = $category->toArray();

        $this->assertSame(['default' => '<p>All electronics</p>'], $array['description']);
        $this->assertSame(['default' => 'Electronics | Shop'], $array['seo_title']);
        $this->assertSame(['default' => 'Best electronics'], $array['seo_description']);
        $this->assertSame(['https://example.com/cat.jpg'], $array['images']);
    }

    public function testToArrayExcludesEmptyImages(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Electronics'),
            url: LocalizedString::create('https://example.com/electronics'),
            images: [],
        );

        $array = $category->toArray();

        $this->assertArrayNotHasKey('images', $array);
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 'CAT-001',
            'is_visible' => true,
            'name' => ['default' => 'Electronics', 'cs' => 'Elektronika'],
            'url' => ['default' => 'https://example.com/electronics'],
            'description' => ['default' => 'Description'],
            'images' => ['https://example.com/cat.jpg'],
            'guid' => '550e8400-e29b-41d4-a716-446655440000',
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $category = Category::fromArray($data);

        $this->assertSame('CAT-001', $category->id);
        $this->assertTrue($category->isVisible);
        $this->assertSame('Electronics', $category->name->getDefault());
        $this->assertSame('Description', $category->description?->getDefault());
        $this->assertSame(['https://example.com/cat.jpg'], $category->images);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $category->guid);
        $this->assertInstanceOf(\DateTimeInterface::class, $category->createdAt);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 'CAT-001',
            'is_visible' => false,
            'name' => ['default' => 'Test'],
            'url' => ['default' => 'https://example.com'],
        ];

        $category = Category::fromArray($data);

        $this->assertSame('CAT-001', $category->id);
        $this->assertFalse($category->isVisible);
        $this->assertNull($category->description);
        $this->assertSame([], $category->images);
        $this->assertNull($category->guid);
    }
}

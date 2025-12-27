<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

final class CategoryTest extends TestCase
{
    public function testCategoryFromArrayWithContent(): void
    {
        $data = [
            'id' => 'CAT-001',
            'is_visible' => true,
            'name' => ['default' => 'Category Name', 'cs' => 'Název kategorie'],
            'url' => ['default' => 'https://example.com/category'],
            'description' => ['default' => '<p>Category description</p>'],
            'content' => [
                'html' => [
                    'cs' => '<div class="pobo-content">Czech HTML</div>',
                    'sk' => '<div class="pobo-content">Slovak HTML</div>',
                ],
                'marketplace' => [
                    'cs' => '<div>Czech Marketplace</div>',
                ],
            ],
            'images' => ['https://example.com/image.jpg'],
            'guid' => '550e8400-e29b-41d4-a716-446655440000',
            'is_loaded' => false,
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $category = Category::fromArray($data);

        $this->assertSame('CAT-001', $category->id);
        $this->assertTrue($category->isVisible);
        $this->assertSame('Category Name', $category->name->getDefault());
        $this->assertSame('Název kategorie', $category->name->get(Language::CS));

        $this->assertInstanceOf(Content::class, $category->content);
        $this->assertSame('<div class="pobo-content">Czech HTML</div>', $category->content->getHtml(Language::CS));
        $this->assertSame('<div class="pobo-content">Slovak HTML</div>', $category->content->getHtml(Language::SK));
        $this->assertSame('<div>Czech Marketplace</div>', $category->content->getMarketplace(Language::CS));

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $category->guid);
        $this->assertFalse($category->isLoaded);
    }

    public function testCategoryFromArrayWithoutContent(): void
    {
        $data = [
            'id' => 'CAT-002',
            'is_visible' => true,
            'name' => ['default' => 'Category'],
            'url' => ['default' => 'https://example.com'],
        ];

        $category = Category::fromArray($data);

        $this->assertNull($category->content);
    }

    public function testCategoryToArrayDoesNotIncludeContent(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Category'),
            url: LocalizedString::create('https://example.com'),
            content: new Content(
                html: ['cs' => '<div>Test</div>'],
                marketplace: [],
            ),
        );

        $array = $category->toArray();

        $this->assertArrayNotHasKey('content', $array);
    }

    public function testCategoryWithAllLocalizedFields(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Category')
                ->withTranslation(Language::CS, 'Kategorie')
                ->withTranslation(Language::SK, 'Kategória'),
            url: LocalizedString::create('https://example.com/category')
                ->withTranslation(Language::CS, 'https://example.com/cs/kategorie')
                ->withTranslation(Language::SK, 'https://example.com/sk/kategoria'),
            description: LocalizedString::create('<p>Description</p>')
                ->withTranslation(Language::CS, '<p>Popis</p>'),
            seoTitle: LocalizedString::create('SEO Title')
                ->withTranslation(Language::CS, 'SEO Titulek'),
            seoDescription: LocalizedString::create('SEO Description')
                ->withTranslation(Language::CS, 'SEO Popis'),
        );

        $array = $category->toArray();

        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('seo_title', $array);
        $this->assertArrayHasKey('seo_description', $array);
    }

    public function testCategoryWithImages(): void
    {
        $images = [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',
        ];

        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Category'),
            url: LocalizedString::create('https://example.com'),
            images: $images,
        );

        $array = $category->toArray();

        $this->assertSame($images, $array['images']);
    }

    public function testCategoryToArrayExcludesNullFields(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Category'),
            url: LocalizedString::create('https://example.com'),
        );

        $array = $category->toArray();

        $this->assertArrayNotHasKey('description', $array);
        $this->assertArrayNotHasKey('seo_title', $array);
        $this->assertArrayNotHasKey('seo_description', $array);
        $this->assertArrayNotHasKey('images', $array);
    }

    public function testCategoryTimestamps(): void
    {
        $data = [
            'id' => 'CAT-001',
            'is_visible' => true,
            'name' => ['default' => 'Category'],
            'url' => ['default' => 'https://example.com'],
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $category = Category::fromArray($data);

        $this->assertInstanceOf(\DateTimeInterface::class, $category->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $category->updatedAt);
        $this->assertSame('2024-01-15', $category->createdAt->format('Y-m-d'));
        $this->assertSame('2024-01-16', $category->updatedAt->format('Y-m-d'));
    }
}

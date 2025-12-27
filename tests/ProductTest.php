<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\Language;

final class ProductTest extends TestCase
{
    public function testProductFromArrayWithContent(): void
    {
        $data = [
            'id' => 'PROD-001',
            'is_visible' => true,
            'name' => ['default' => 'Product Name', 'cs' => 'Název produktu'],
            'url' => ['default' => 'https://example.com/product'],
            'short_description' => ['default' => 'Short description'],
            'description' => ['default' => '<p>Full description</p>'],
            'content' => [
                'html' => [
                    'cs' => '<div class="pobo-content">Czech HTML</div>',
                    'sk' => '<div class="pobo-content">Slovak HTML</div>',
                    'en' => '<div class="pobo-content">English HTML</div>',
                ],
                'marketplace' => [
                    'cs' => '<div>Czech Marketplace</div>',
                    'sk' => '<div>Slovak Marketplace</div>',
                ],
            ],
            'images' => ['https://example.com/image.jpg'],
            'categories' => [
                ['id' => 'CAT-001', 'name' => ['default' => 'Category 1']],
            ],
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $product = Product::fromArray($data);

        $this->assertSame('PROD-001', $product->id);
        $this->assertTrue($product->isVisible);
        $this->assertSame('Product Name', $product->name->getDefault());
        $this->assertSame('Název produktu', $product->name->get(Language::CS));

        $this->assertInstanceOf(Content::class, $product->content);
        $this->assertSame('<div class="pobo-content">Czech HTML</div>', $product->content->getHtml(Language::CS));
        $this->assertSame('<div class="pobo-content">Slovak HTML</div>', $product->content->getHtml(Language::SK));
        $this->assertSame('<div class="pobo-content">English HTML</div>', $product->content->getHtml(Language::EN));
        $this->assertSame('<div>Czech Marketplace</div>', $product->content->getMarketplace(Language::CS));
    }

    public function testProductFromArrayWithoutContent(): void
    {
        $data = [
            'id' => 'PROD-002',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
        ];

        $product = Product::fromArray($data);

        $this->assertNull($product->content);
    }

    public function testProductToArrayDoesNotIncludeContent(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            content: new Content(
                html: ['cs' => '<div>Test</div>'],
                marketplace: [],
            ),
        );

        $array = $product->toArray();

        $this->assertArrayNotHasKey('content', $array);
    }

    public function testProductWithAllLocalizedFields(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product')
                ->withTranslation(Language::CS, 'Produkt')
                ->withTranslation(Language::SK, 'Produkt'),
            url: LocalizedString::create('https://example.com/product')
                ->withTranslation(Language::CS, 'https://example.com/cs/produkt')
                ->withTranslation(Language::SK, 'https://example.com/sk/produkt'),
            shortDescription: LocalizedString::create('Short')
                ->withTranslation(Language::CS, 'Krátký'),
            description: LocalizedString::create('<p>Full</p>')
                ->withTranslation(Language::CS, '<p>Plný</p>'),
            seoTitle: LocalizedString::create('SEO Title')
                ->withTranslation(Language::CS, 'SEO Titulek'),
            seoDescription: LocalizedString::create('SEO Description')
                ->withTranslation(Language::CS, 'SEO Popis'),
        );

        $array = $product->toArray();

        $this->assertArrayHasKey('short_description', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('seo_title', $array);
        $this->assertArrayHasKey('seo_description', $array);
    }

    public function testProductWithCategoriesAndParameters(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            categoriesIds: ['CAT-001', 'CAT-002'],
            parametersIds: [1, 2, 3],
        );

        $array = $product->toArray();

        $this->assertSame(['CAT-001', 'CAT-002'], $array['categories_ids']);
        $this->assertSame([1, 2, 3], $array['parameters_ids']);
    }

    public function testProductWithImages(): void
    {
        $images = [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',
        ];

        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            images: $images,
        );

        $array = $product->toArray();

        $this->assertSame($images, $array['images']);
    }

    public function testProductWithGuidAndIsLoaded(): void
    {
        $data = [
            'id' => 'PROD-001',
            'guid' => '550e8400-e29b-41d4-a716-446655440000',
            'is_visible' => true,
            'is_loaded' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
        ];

        $product = Product::fromArray($data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $product->guid);
        $this->assertTrue($product->isLoaded);
    }
}

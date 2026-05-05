<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\RichSnippet;
use Pobo\Sdk\DTO\SiteLink;
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

    public function testProductFromArrayWithNestedContent(): void
    {
        $nested = [
            [['id' => 2, 'class' => 'empty', 'tag' => 'div', 'children' => []]],
            [['id' => 5, 'class' => 'text', 'tag' => 'p', 'children' => []]],
        ];

        $data = [
            'id' => 'PROD-003',
            'is_visible' => true,
            'name' => ['default' => 'Product with nested'],
            'url' => ['default' => 'https://example.com/product'],
            'content' => [
                'html' => [
                    'default' => '<div class="pobo-content">Default HTML</div>',
                    'cs' => '<div class="pobo-content">Czech HTML</div>',
                ],
                'marketplace' => [
                    'default' => '<div>Default Marketplace</div>',
                ],
                'nested' => $nested,
            ],
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(Content::class, $product->content);
        $this->assertSame($nested, $product->content->getNested());
        $this->assertCount(2, $product->content->nested);
        $this->assertSame('<div class="pobo-content">Czech HTML</div>', $product->content->getHtml(Language::CS));
        $this->assertSame('<div>Default Marketplace</div>', $product->content->getMarketplaceDefault());
    }

    public function testProductFromArrayWithContentOnlyHtml(): void
    {
        $data = [
            'id' => 'PROD-004',
            'is_visible' => true,
            'name' => ['default' => 'Product HTML only'],
            'url' => ['default' => 'https://example.com/product'],
            'content' => [
                'html' => [
                    'default' => '<div>HTML</div>',
                ],
            ],
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(Content::class, $product->content);
        $this->assertSame('<div>HTML</div>', $product->content->getHtmlDefault());
        $this->assertSame([], $product->content->marketplace);
        $this->assertSame([], $product->content->nested);
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

    public function testProductFromArrayWithSiteLink(): void
    {
        $data = [
            'id' => 'PROD-SITE',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
            'site_link' => [
                'html' => ['default' => '<div id="pobo-site-link"><nav><a href="#nadpis">Nadpis</a></nav></div>'],
                'list' => [
                    'default' => [
                        ['heading' => 'Nadpis', 'slug' => 'nadpis'],
                    ],
                ],
            ],
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(SiteLink::class, $product->siteLink);
        $this->assertStringContainsString('pobo-site-link', $product->siteLink->getHtml(Language::DEFAULT));
        $this->assertSame('nadpis', $product->siteLink->getList(Language::DEFAULT)[0]->slug);
    }

    public function testProductFromArrayWithRichSnippet(): void
    {
        $data = [
            'id' => 'PROD-RICH',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
            'rich_snippet' => [
                'html' => ['default' => '<script type="application/ld+json">{"@type":"FAQPage"}</script>'],
                'json' => [
                    'default' => ['@type' => 'FAQPage', 'mainEntity' => []],
                ],
            ],
        ];

        $product = Product::fromArray($data);

        $this->assertInstanceOf(RichSnippet::class, $product->richSnippet);
        $this->assertStringContainsString('FAQPage', $product->richSnippet->getHtml(Language::DEFAULT));
        $this->assertSame('FAQPage', $product->richSnippet->getJson(Language::DEFAULT)['@type']);
    }

    public function testProductFromArrayWithoutSiteLinkAndRichSnippet(): void
    {
        $data = [
            'id' => 'PROD-PLAIN',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
        ];

        $product = Product::fromArray($data);

        $this->assertNull($product->siteLink);
        $this->assertNull($product->richSnippet);
    }

    public function testProductToArrayOmitsBrandIdByDefault(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
        );

        $array = $product->toArray();

        $this->assertArrayNotHasKey('brand_id', $array);
    }

    public function testProductToArrayIncludesBrandIdString(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            brandId: 'BRAND-001',
        );

        $array = $product->toArray();

        $this->assertSame('BRAND-001', $array['brand_id']);
    }

    public function testProductToArrayIncludesBrandIdNullToUnset(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            brandId: null,
        );

        $array = $product->toArray();

        $this->assertArrayHasKey('brand_id', $array);
        $this->assertNull($array['brand_id']);
    }

    public function testProductToArrayWithExplicitBrandIdUnsetSentinel(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com'),
            brandId: Product::BRAND_ID_UNSET,
        );

        $array = $product->toArray();

        $this->assertArrayNotHasKey('brand_id', $array);
    }

    public function testProductFromArrayWithBrandIdString(): void
    {
        $data = [
            'id' => 'PROD-BRAND',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
            'brand_id' => 'BRAND-001',
        ];

        $product = Product::fromArray($data);

        $this->assertSame('BRAND-001', $product->brandId);
    }

    public function testProductFromArrayWithBrandIdNull(): void
    {
        $data = [
            'id' => 'PROD-NOBRAND',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
            'brand_id' => null,
        ];

        $product = Product::fromArray($data);

        $this->assertNull($product->brandId);
    }

    public function testProductFromArrayWithoutBrandIdKeyKeepsSentinel(): void
    {
        $data = [
            'id' => 'PROD-LEGACY',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
        ];

        $product = Product::fromArray($data);

        $this->assertSame(Product::BRAND_ID_UNSET, $product->brandId);
        $this->assertArrayNotHasKey('brand_id', $product->toArray());
    }

    public function testProductFromArrayThenToArrayRoundtripsBrandId(): void
    {
        $data = [
            'id' => 'PROD-RT',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
            'brand_id' => 'BRAND-RT',
        ];

        $array = Product::fromArray($data)->toArray();

        $this->assertSame('BRAND-RT', $array['brand_id']);
    }
}

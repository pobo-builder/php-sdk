<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\RichSnippet;
use Pobo\Sdk\DTO\SiteLink;
use Pobo\Sdk\Enum\Language;

final class BrandTest extends TestCase
{
    public function testBrandFromArrayWithContent(): void
    {
        $data = [
            'id' => 'BRAND-001',
            'is_visible' => true,
            'image_preview' => 'https://example.com/brands/apple-logo.png',
            'name' => ['default' => 'Apple', 'cs' => 'Apple CZ'],
            'url' => ['default' => 'https://example.com/znacky/apple'],
            'description' => ['default' => '<p>Brand description</p>'],
            'content' => [
                'html' => [
                    'cs' => '<div class="pobo-content">Czech HTML</div>',
                    'sk' => '<div class="pobo-content">Slovak HTML</div>',
                ],
                'marketplace' => [
                    'cs' => '<div>Czech Marketplace</div>',
                ],
            ],
            'guid' => '550e8400-e29b-41d4-a716-446655440099',
            'is_loaded' => false,
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $brand = Brand::fromArray($data);

        $this->assertSame('BRAND-001', $brand->id);
        $this->assertTrue($brand->isVisible);
        $this->assertSame('https://example.com/brands/apple-logo.png', $brand->imagePreview);
        $this->assertSame('Apple', $brand->name->getDefault());
        $this->assertSame('Apple CZ', $brand->name->get(Language::CS));

        $this->assertInstanceOf(Content::class, $brand->content);
        $this->assertSame('<div class="pobo-content">Czech HTML</div>', $brand->content->getHtml(Language::CS));
        $this->assertSame('<div>Czech Marketplace</div>', $brand->content->getMarketplace(Language::CS));

        $this->assertSame('550e8400-e29b-41d4-a716-446655440099', $brand->guid);
        $this->assertFalse($brand->isLoaded);
    }

    public function testBrandFromArrayWithNullImagePreview(): void
    {
        $data = [
            'id' => 'BRAND-002',
            'is_visible' => true,
            'image_preview' => null,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com/brand'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->imagePreview);
    }

    public function testBrandFromArrayWithoutImagePreviewKey(): void
    {
        $data = [
            'id' => 'BRAND-003',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com/brand'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->imagePreview);
    }

    public function testBrandFromArrayWithoutContent(): void
    {
        $data = [
            'id' => 'BRAND-004',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->content);
    }

    public function testBrandToArrayWithRequiredFieldsOnly(): void
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

    public function testBrandToArrayIncludesImagePreview(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple'),
            url: LocalizedString::create('https://example.com/znacky/apple'),
            imagePreview: 'https://example.com/brands/apple-logo.png',
        );

        $array = $brand->toArray();

        $this->assertSame('https://example.com/brands/apple-logo.png', $array['image_preview']);
    }

    public function testBrandToArrayOmitsImagePreviewWhenNull(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple'),
            url: LocalizedString::create('https://example.com/znacky/apple'),
        );

        $array = $brand->toArray();

        $this->assertArrayNotHasKey('image_preview', $array);
    }

    public function testBrandToArrayDoesNotIncludeContent(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Brand'),
            url: LocalizedString::create('https://example.com'),
            content: new Content(
                html: ['cs' => '<div>Test</div>'],
                marketplace: [],
            ),
        );

        $array = $brand->toArray();

        $this->assertArrayNotHasKey('content', $array);
    }

    public function testBrandWithAllLocalizedFields(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Apple')
                ->withTranslation(Language::CS, 'Apple CZ')
                ->withTranslation(Language::SK, 'Apple SK'),
            url: LocalizedString::create('https://example.com/znacky/apple')
                ->withTranslation(Language::CS, 'https://example.com/cs/znacky/apple')
                ->withTranslation(Language::SK, 'https://example.com/sk/znacky/apple'),
            description: LocalizedString::create('<p>Description</p>')
                ->withTranslation(Language::CS, '<p>Popis</p>'),
            seoTitle: LocalizedString::create('SEO Title')
                ->withTranslation(Language::CS, 'SEO Titulek'),
            seoDescription: LocalizedString::create('SEO Description')
                ->withTranslation(Language::CS, 'SEO Popis'),
        );

        $array = $brand->toArray();

        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('seo_title', $array);
        $this->assertArrayHasKey('seo_description', $array);
    }

    public function testBrandToArrayExcludesNullFields(): void
    {
        $brand = new Brand(
            id: 'BRAND-001',
            isVisible: true,
            name: LocalizedString::create('Brand'),
            url: LocalizedString::create('https://example.com'),
        );

        $array = $brand->toArray();

        $this->assertArrayNotHasKey('description', $array);
        $this->assertArrayNotHasKey('seo_title', $array);
        $this->assertArrayNotHasKey('seo_description', $array);
        $this->assertArrayNotHasKey('image_preview', $array);
    }

    public function testBrandTimestamps(): void
    {
        $data = [
            'id' => 'BRAND-001',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $brand = Brand::fromArray($data);

        $this->assertInstanceOf(\DateTimeInterface::class, $brand->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $brand->updatedAt);
        $this->assertSame('2024-01-15', $brand->createdAt->format('Y-m-d'));
        $this->assertSame('2024-01-16', $brand->updatedAt->format('Y-m-d'));
    }

    public function testBrandFromArrayWithRichSnippet(): void
    {
        $data = [
            'id' => 'BRAND-RICH',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
            'rich_snippet' => [
                'html' => ['default' => '<script type="application/ld+json">{"@type":"FAQPage"}</script>'],
                'json' => ['default' => ['@type' => 'FAQPage', 'mainEntity' => []]],
            ],
        ];

        $brand = Brand::fromArray($data);

        $this->assertInstanceOf(RichSnippet::class, $brand->richSnippet);
        $this->assertSame('FAQPage', $brand->richSnippet->getJson(Language::DEFAULT)['@type']);
    }

    public function testBrandFromArrayWithoutRichSnippet(): void
    {
        $data = [
            'id' => 'BRAND-PLAIN',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->richSnippet);
    }

    public function testBrandFromArrayWithSiteLink(): void
    {
        $data = [
            'id' => 'BRAND-SITE',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
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

        $brand = Brand::fromArray($data);

        $this->assertInstanceOf(SiteLink::class, $brand->siteLink);
        $this->assertStringContainsString('pobo-site-link', $brand->siteLink->getHtml(Language::DEFAULT));
        $this->assertSame('nadpis', $brand->siteLink->getList(Language::DEFAULT)[0]->slug);
    }

    public function testBrandFromArrayWithoutSiteLink(): void
    {
        $data = [
            'id' => 'BRAND-PLAIN',
            'is_visible' => true,
            'name' => ['default' => 'Brand'],
            'url' => ['default' => 'https://example.com'],
        ];

        $brand = Brand::fromArray($data);

        $this->assertNull($brand->siteLink);
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

final class BlogTest extends TestCase
{
    public function testCreateBlogWithAllFields(): void
    {
        $blog = new Blog(
            id: 123,
            guid: '550e8400-e29b-41d4-a716-446655440000',
            category: 'news',
            isVisible: true,
            name: LocalizedString::create('Blog Title')
                ->withTranslation(Language::CS, 'Název blogu'),
            url: LocalizedString::create('https://example.com/blog')
                ->withTranslation(Language::CS, 'https://example.com/cs/blog'),
            description: LocalizedString::create('<p>Description</p>'),
            seoTitle: LocalizedString::create('SEO Title'),
            seoDescription: LocalizedString::create('SEO Description'),
            images: ['https://example.com/image.jpg'],
        );

        $this->assertSame(123, $blog->id);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $blog->guid);
        $this->assertSame('news', $blog->category);
        $this->assertTrue($blog->isVisible);
        $this->assertSame('Blog Title', $blog->name->getDefault());
        $this->assertSame('Název blogu', $blog->name->get(Language::CS));
    }

    public function testBlogToArray(): void
    {
        $blog = new Blog(
            guid: '550e8400-e29b-41d4-a716-446655440000',
            category: 'tips',
            isVisible: true,
            name: LocalizedString::create('Blog Title'),
            url: LocalizedString::create('https://example.com/blog'),
            description: LocalizedString::create('<p>Description</p>'),
            images: ['https://example.com/image.jpg'],
        );

        $array = $blog->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $array['guid']);
        $this->assertSame('tips', $array['category']);
        $this->assertTrue($array['is_visible']);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('url', $array);
        $this->assertArrayHasKey('description', $array);
        $this->assertArrayHasKey('images', $array);
    }

    public function testBlogToArrayExcludesNullFields(): void
    {
        $blog = new Blog(
            isVisible: true,
            name: LocalizedString::create('Blog Title'),
            url: LocalizedString::create('https://example.com/blog'),
        );

        $array = $blog->toArray();

        $this->assertArrayNotHasKey('guid', $array);
        $this->assertArrayNotHasKey('category', $array);
        $this->assertArrayNotHasKey('description', $array);
        $this->assertArrayNotHasKey('seo_title', $array);
        $this->assertArrayNotHasKey('seo_description', $array);
        $this->assertArrayNotHasKey('images', $array);
    }

    public function testBlogFromArray(): void
    {
        $data = [
            'id' => 456,
            'guid' => '550e8400-e29b-41d4-a716-446655440001',
            'category' => 'news',
            'is_visible' => true,
            'name' => ['default' => 'Blog Title', 'cs' => 'Název blogu'],
            'url' => ['default' => 'https://example.com/blog'],
            'description' => ['default' => '<p>Description</p>'],
            'seo_title' => ['default' => 'SEO Title'],
            'seo_description' => ['default' => 'SEO Description'],
            'images' => ['https://example.com/image.jpg'],
            'is_loaded' => false,
            'created_at' => '2024-01-15T10:30:00.000000Z',
            'updated_at' => '2024-01-16T14:20:00.000000Z',
        ];

        $blog = Blog::fromArray($data);

        $this->assertSame(456, $blog->id);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440001', $blog->guid);
        $this->assertSame('news', $blog->category);
        $this->assertTrue($blog->isVisible);
        $this->assertSame('Blog Title', $blog->name->getDefault());
        $this->assertSame('Název blogu', $blog->name->get(Language::CS));
        $this->assertFalse($blog->isLoaded);
        $this->assertInstanceOf(\DateTimeInterface::class, $blog->createdAt);
        $this->assertInstanceOf(\DateTimeInterface::class, $blog->updatedAt);
    }

    public function testBlogFromArrayWithContent(): void
    {
        $data = [
            'id' => 789,
            'is_visible' => true,
            'name' => ['default' => 'Blog'],
            'url' => ['default' => 'https://example.com'],
            'content' => [
                'html' => [
                    'cs' => '<div class="pobo-content">Czech HTML</div>',
                    'sk' => '<div class="pobo-content">Slovak HTML</div>',
                ],
                'marketplace' => [
                    'cs' => '<div>Czech Marketplace</div>',
                ],
            ],
        ];

        $blog = Blog::fromArray($data);

        $this->assertInstanceOf(Content::class, $blog->content);
        $this->assertSame('<div class="pobo-content">Czech HTML</div>', $blog->content->getHtml(Language::CS));
        $this->assertSame('<div class="pobo-content">Slovak HTML</div>', $blog->content->getHtml(Language::SK));
        $this->assertSame('<div>Czech Marketplace</div>', $blog->content->getMarketplace(Language::CS));
    }

    public function testBlogFromArrayWithMinimalData(): void
    {
        $data = [
            'id' => 1,
            'is_visible' => false,
            'name' => ['default' => 'Minimal Blog'],
            'url' => ['default' => 'https://example.com/minimal'],
        ];

        $blog = Blog::fromArray($data);

        $this->assertSame(1, $blog->id);
        $this->assertFalse($blog->isVisible);
        $this->assertNull($blog->guid);
        $this->assertNull($blog->category);
        $this->assertNull($blog->description);
        $this->assertNull($blog->seoTitle);
        $this->assertNull($blog->seoDescription);
        $this->assertNull($blog->content);
        $this->assertSame([], $blog->images);
    }

    public function testBlogDefaultIsVisible(): void
    {
        $blog = new Blog();

        $this->assertTrue($blog->isVisible);
    }

    public function testBlogImagesArray(): void
    {
        $images = [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',
            'https://example.com/image3.jpg',
        ];

        $blog = new Blog(
            isVisible: true,
            name: LocalizedString::create('Blog'),
            url: LocalizedString::create('https://example.com'),
            images: $images,
        );

        $this->assertCount(3, $blog->images);
        $this->assertSame($images, $blog->images);

        $array = $blog->toArray();
        $this->assertSame($images, $array['images']);
    }
}

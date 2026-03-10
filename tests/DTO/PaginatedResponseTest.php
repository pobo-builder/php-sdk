<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\PaginatedResponse;
use Pobo\Sdk\DTO\Product;

final class PaginatedResponseTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'data' => [
                [
                    'id' => 'PROD-001',
                    'is_visible' => true,
                    'name' => ['default' => 'Product 1'],
                    'url' => ['default' => 'https://example.com/1'],
                ],
                [
                    'id' => 'PROD-002',
                    'is_visible' => false,
                    'name' => ['default' => 'Product 2'],
                    'url' => ['default' => 'https://example.com/2'],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'per_page' => 100,
                'total' => 250,
            ],
        ];

        $response = PaginatedResponse::fromArray($data, Product::class);

        $this->assertCount(2, $response->data);
        $this->assertInstanceOf(Product::class, $response->data[0]);
        $this->assertSame('PROD-001', $response->data[0]->id);
        $this->assertSame('PROD-002', $response->data[1]->id);
        $this->assertSame(1, $response->currentPage);
        $this->assertSame(100, $response->perPage);
        $this->assertSame(250, $response->total);
    }

    public function testHasMorePages(): void
    {
        $responseWithMore = new PaginatedResponse(
            data: [],
            currentPage: 1,
            perPage: 100,
            total: 250,
        );

        $responseLastPage = new PaginatedResponse(
            data: [],
            currentPage: 3,
            perPage: 100,
            total: 250,
        );

        $responseExact = new PaginatedResponse(
            data: [],
            currentPage: 1,
            perPage: 100,
            total: 100,
        );

        $this->assertTrue($responseWithMore->hasMorePages());
        $this->assertFalse($responseLastPage->hasMorePages());
        $this->assertFalse($responseExact->hasMorePages());
    }

    public function testGetTotalPages(): void
    {
        $response1 = new PaginatedResponse(data: [], currentPage: 1, perPage: 100, total: 250);
        $response2 = new PaginatedResponse(data: [], currentPage: 1, perPage: 100, total: 100);
        $response3 = new PaginatedResponse(data: [], currentPage: 1, perPage: 100, total: 50);
        $response4 = new PaginatedResponse(data: [], currentPage: 1, perPage: 100, total: 0);

        $this->assertSame(3, $response1->getTotalPages());
        $this->assertSame(1, $response2->getTotalPages());
        $this->assertSame(1, $response3->getTotalPages());
        $this->assertSame(0, $response4->getTotalPages());
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'data' => [],
        ];

        $response = PaginatedResponse::fromArray($data, Product::class);

        $this->assertSame([], $response->data);
        $this->assertSame(1, $response->currentPage);
        $this->assertSame(100, $response->perPage);
        $this->assertSame(0, $response->total);
    }

    public function testFromArrayWithNestedContent(): void
    {
        $nested = [[['id' => 2, 'class' => 'empty', 'tag' => 'div', 'children' => []]]];

        $data = [
            'data' => [
                [
                    'id' => 'PROD-001',
                    'is_visible' => true,
                    'name' => ['default' => 'Product 1'],
                    'url' => ['default' => 'https://example.com/1'],
                    'content' => [
                        'html' => ['default' => '<div>HTML</div>'],
                        'marketplace' => ['default' => '<div>MP</div>'],
                        'nested' => $nested,
                    ],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'per_page' => 100,
                'total' => 1,
            ],
        ];

        $response = PaginatedResponse::fromArray($data, Product::class);

        $this->assertCount(1, $response->data);
        $product = $response->data[0];
        $this->assertNotNull($product->content);
        $this->assertSame('<div>HTML</div>', $product->content->getHtmlDefault());
        $this->assertSame('<div>MP</div>', $product->content->getMarketplaceDefault());
        $this->assertSame($nested, $product->content->getNested());
    }

    public function testFromArrayWithContentOnlyHtml(): void
    {
        $data = [
            'data' => [
                [
                    'id' => 'PROD-002',
                    'is_visible' => true,
                    'name' => ['default' => 'Product 2'],
                    'url' => ['default' => 'https://example.com/2'],
                    'content' => [
                        'html' => ['default' => '<div>HTML only</div>'],
                    ],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'per_page' => 100,
                'total' => 1,
            ],
        ];

        $response = PaginatedResponse::fromArray($data, Product::class);

        $product = $response->data[0];
        $this->assertNotNull($product->content);
        $this->assertSame('<div>HTML only</div>', $product->content->getHtmlDefault());
        $this->assertSame([], $product->content->marketplace);
        $this->assertSame([], $product->content->nested);
    }

    public function testFromArrayWithCategoryEntity(): void
    {
        $data = [
            'data' => [
                [
                    'id' => 'CAT-001',
                    'is_visible' => true,
                    'name' => ['default' => 'Category 1'],
                    'url' => ['default' => 'https://example.com/cat'],
                    'content' => [
                        'html' => ['default' => '<div>Cat HTML</div>'],
                        'nested' => [[['id' => 1, 'tag' => 'div']]],
                    ],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'per_page' => 100,
                'total' => 1,
            ],
        ];

        $response = PaginatedResponse::fromArray($data, Category::class);

        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(Category::class, $response->data[0]);
        $this->assertNotNull($response->data[0]->content);
        $this->assertCount(1, $response->data[0]->content->nested);
    }

    public function testFromArrayWithBlogEntity(): void
    {
        $data = [
            'data' => [
                [
                    'id' => 'BLOG-001',
                    'is_visible' => true,
                    'name' => ['default' => 'Blog 1'],
                    'url' => ['default' => 'https://example.com/blog'],
                    'content' => [
                        'html' => ['default' => '<div>Blog HTML</div>'],
                        'marketplace' => ['default' => '<div>Blog MP</div>'],
                        'nested' => [[['id' => 1, 'tag' => 'p']]],
                    ],
                ],
            ],
            'meta' => [
                'current_page' => 1,
                'per_page' => 100,
                'total' => 1,
            ],
        ];

        $response = PaginatedResponse::fromArray($data, Blog::class);

        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(Blog::class, $response->data[0]);
        $blog = $response->data[0];
        $this->assertNotNull($blog->content);
        $this->assertSame('<div>Blog HTML</div>', $blog->content->getHtmlDefault());
        $this->assertSame('<div>Blog MP</div>', $blog->content->getMarketplaceDefault());
        $this->assertCount(1, $blog->content->nested);
    }
}

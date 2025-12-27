<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
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
}

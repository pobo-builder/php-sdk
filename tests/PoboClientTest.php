<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\ParameterValue;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Exception\ValidationException;
use Pobo\Sdk\PoboClient;

final class PoboClientTest extends TestCase
{
    private PoboClient $client;

    protected function setUp(): void
    {
        $this->client = new PoboClient(
            apiToken: 'test-token',
            baseUrl: 'https://api.pobo.space',
        );
    }

    public function testImportProductsThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Payload cannot be empty');

        $this->client->importProducts([]);
    }

    public function testImportProductsThrowsExceptionForTooManyItems(): void
    {
        $products = [];
        for ($i = 0; $i < 101; $i++) {
            $products[] = [
                'id' => sprintf('PROD-%03d', $i),
                'is_visible' => true,
                'name' => ['default' => sprintf('Product %d', $i)],
                'url' => ['default' => sprintf('https://example.com/%d', $i)],
            ];
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Too many items: 101 provided, maximum is 100');

        $this->client->importProducts($products);
    }

    public function testImportCategoriesThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Payload cannot be empty');

        $this->client->importCategories([]);
    }

    public function testImportCategoriesThrowsExceptionForTooManyItems(): void
    {
        $categories = [];
        for ($i = 0; $i < 101; $i++) {
            $categories[] = [
                'id' => sprintf('CAT-%03d', $i),
                'is_visible' => true,
                'name' => ['default' => sprintf('Category %d', $i)],
                'url' => ['default' => sprintf('https://example.com/cat/%d', $i)],
            ];
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Too many items: 101 provided, maximum is 100');

        $this->client->importCategories($categories);
    }

    public function testImportParametersThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Payload cannot be empty');

        $this->client->importParameters([]);
    }

    public function testImportParametersThrowsExceptionForTooManyItems(): void
    {
        $parameters = [];
        for ($i = 0; $i < 101; $i++) {
            $parameters[] = [
                'id' => $i,
                'name' => sprintf('Parameter %d', $i),
                'values' => [['id' => 1, 'value' => 'Value']],
            ];
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Too many items: 101 provided, maximum is 100');

        $this->client->importParameters($parameters);
    }

    public function testProductDtoIsConvertedToArray(): void
    {
        $product = new Product(
            id: 'PROD-001',
            isVisible: true,
            name: LocalizedString::create('Test Product'),
            url: LocalizedString::create('https://example.com/product'),
        );

        // We can't test the actual API call without mocking,
        // but we can verify the DTO conversion works
        $array = $product->toArray();

        $this->assertSame('PROD-001', $array['id']);
        $this->assertTrue($array['is_visible']);
    }

    public function testCategoryDtoIsConvertedToArray(): void
    {
        $category = new Category(
            id: 'CAT-001',
            isVisible: true,
            name: LocalizedString::create('Test Category'),
            url: LocalizedString::create('https://example.com/category'),
        );

        $array = $category->toArray();

        $this->assertSame('CAT-001', $array['id']);
        $this->assertTrue($array['is_visible']);
    }

    public function testParameterDtoIsConvertedToArray(): void
    {
        $parameter = new Parameter(
            id: 1,
            name: 'Color',
            values: [
                new ParameterValue(id: 1, value: 'Red'),
                new ParameterValue(id: 2, value: 'Blue'),
            ],
        );

        $array = $parameter->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame('Color', $array['name']);
        $this->assertCount(2, $array['values']);
    }

    public function testClientAcceptsCustomTimeout(): void
    {
        $client = new PoboClient(
            apiToken: 'test-token',
            baseUrl: 'https://api.pobo.space',
            timeout: 60,
        );

        // Client should be created without errors
        $this->assertInstanceOf(PoboClient::class, $client);
    }

    public function testClientAcceptsCustomBaseUrl(): void
    {
        $client = new PoboClient(
            apiToken: 'test-token',
            baseUrl: 'https://custom.api.example.com',
        );

        $this->assertInstanceOf(PoboClient::class, $client);
    }

    public function testValidationExceptionContainsErrors(): void
    {
        try {
            $this->client->importProducts([]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('bulk', $e->errors);
            $this->assertContains('At least one item is required', $e->errors['bulk']);
        }
    }

    public function testMaxItemsValidationExceptionContainsErrors(): void
    {
        $products = array_fill(0, 101, [
            'id' => 'PROD-001',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com'],
        ]);

        try {
            $this->client->importProducts($products);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('bulk', $e->errors);
            $this->assertStringContainsString('Maximum 100 items', $e->errors['bulk'][0]);
        }
    }

    public function testImportBlogsThrowsExceptionForEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Payload cannot be empty');

        $this->client->importBlogs([]);
    }

    public function testImportBlogsThrowsExceptionForTooManyItems(): void
    {
        $blogs = [];
        for ($i = 0; $i < 101; $i++) {
            $blogs[] = [
                'guid' => sprintf('550e8400-e29b-41d4-a716-4466554400%02d', $i),
                'is_visible' => true,
                'name' => ['default' => sprintf('Blog %d', $i)],
                'url' => ['default' => sprintf('https://example.com/blog/%d', $i)],
            ];
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Too many items: 101 provided, maximum is 100');

        $this->client->importBlogs($blogs);
    }

    public function testBlogDtoIsConvertedToArray(): void
    {
        $blog = new Blog(
            guid: '550e8400-e29b-41d4-a716-446655440000',
            category: 'news',
            isVisible: true,
            name: LocalizedString::create('Test Blog'),
            url: LocalizedString::create('https://example.com/blog'),
        );

        $array = $blog->toArray();

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $array['guid']);
        $this->assertSame('news', $array['category']);
        $this->assertTrue($array['is_visible']);
    }
}

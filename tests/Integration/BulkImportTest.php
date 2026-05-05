<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\Enum\Language;

final class BulkImportTest extends IntegrationTestCase
{
    private const BULK_SIZE = 20;

    public function testBulkImportProducts(): void
    {
        $products = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $products[] = $this->makeProduct();
        }
        $expectedIds = array_map(fn($p) => $p->id, $products);

        $importResult = $this->client->importProducts($products);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected errors: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(self::BULK_SIZE, $importResult->imported + $importResult->updated);

        $foundIds = [];
        foreach ($this->client->iterateProducts(isEdited: false, lang: [Language::ALL]) as $product) {
            if (in_array($product->id, $expectedIds, true)) {
                $foundIds[] = $product->id;
            }
            if (count($foundIds) === self::BULK_SIZE) {
                break;
            }
        }

        self::assertCount(self::BULK_SIZE, $foundIds, 'All imported products should be retrievable.');
    }

    public function testBulkImportCategories(): void
    {
        $categories = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $categories[] = $this->makeCategory();
        }
        $expectedIds = array_map(fn($c) => $c->id, $categories);

        $importResult = $this->client->importCategories($categories);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected errors: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(self::BULK_SIZE, $importResult->imported + $importResult->updated);

        $foundIds = [];
        foreach ($this->client->iterateCategories(isEdited: false, lang: [Language::ALL]) as $category) {
            if (in_array($category->id, $expectedIds, true)) {
                $foundIds[] = $category->id;
            }
            if (count($foundIds) === self::BULK_SIZE) {
                break;
            }
        }

        self::assertCount(self::BULK_SIZE, $foundIds, 'All imported categories should be retrievable.');
    }

    public function testBulkImportBlogs(): void
    {
        $blogs = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $blogs[] = $this->makeBlog();
        }
        $expectedIds = array_map(fn($b) => $b->id, $blogs);

        $importResult = $this->client->importBlogs($blogs);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected errors: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(self::BULK_SIZE, $importResult->imported + $importResult->updated);

        $foundIds = [];
        foreach ($this->client->iterateBlogs(isEdited: false, lang: [Language::ALL]) as $blog) {
            if (in_array($blog->id, $expectedIds, true)) {
                $foundIds[] = $blog->id;
            }
            if (count($foundIds) === self::BULK_SIZE) {
                break;
            }
        }

        self::assertCount(self::BULK_SIZE, $foundIds, 'All imported blogs should be retrievable.');
    }

    public function testBulkImportBrands(): void
    {
        $brands = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $brands[] = $this->makeBrand();
        }
        $expectedIds = array_map(fn($b) => $b->id, $brands);

        $importResult = $this->client->importBrands($brands);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected errors: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(self::BULK_SIZE, $importResult->imported + $importResult->updated);

        $foundIds = [];
        foreach ($this->client->iterateBrands(isEdited: false, lang: [Language::ALL]) as $brand) {
            if (in_array($brand->id, $expectedIds, true)) {
                $foundIds[] = $brand->id;
            }
            if (count($foundIds) === self::BULK_SIZE) {
                break;
            }
        }

        self::assertCount(self::BULK_SIZE, $foundIds, 'All imported brands should be retrievable.');
    }
}

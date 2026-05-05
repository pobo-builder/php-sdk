<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\Enum\Language;

final class BulkUpdateTest extends IntegrationTestCase
{
    private const BULK_SIZE = 20;

    public function testReimportProductsTriggersUpdate(): void
    {
        $originals = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $originals[] = $this->makeProduct();
        }

        $firstResult = $this->client->importProducts($originals);

        self::assertSame(self::BULK_SIZE, $firstResult->imported, 'First pass should be a fresh import.');
        self::assertSame(0, $firstResult->updated);
        self::assertFalse($firstResult->hasErrors());

        // Re-build the same items with the same IDs but fresh faker content.
        $updates = array_map(fn($product) => $this->makeProduct($product->id), $originals);

        $secondResult = $this->client->importProducts($updates);

        self::assertSame(0, $secondResult->imported, 'Re-import must not create new records.');
        self::assertSame(self::BULK_SIZE, $secondResult->updated);
        self::assertFalse($secondResult->hasErrors());

        // Verify the new content actually propagated to the server.
        $expectedNamesById = [];
        foreach ($updates as $product) {
            $expectedNamesById[$product->id] = $product->name->getDefault();
        }

        $verified = 0;
        foreach ($this->client->iterateProducts(isEdited: false, lang: [Language::ALL]) as $product) {
            if (isset($expectedNamesById[$product->id])) {
                self::assertSame(
                    $expectedNamesById[$product->id],
                    $product->name->getDefault(),
                    sprintf('Product %s should reflect the updated name.', $product->id),
                );
                $verified++;
            }
            if ($verified === self::BULK_SIZE) {
                break;
            }
        }

        self::assertSame(self::BULK_SIZE, $verified, 'All updated products should be readable.');
    }

    public function testReimportCategoriesTriggersUpdate(): void
    {
        $originals = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $originals[] = $this->makeCategory();
        }

        $firstResult = $this->client->importCategories($originals);

        self::assertSame(self::BULK_SIZE, $firstResult->imported);
        self::assertSame(0, $firstResult->updated);
        self::assertFalse($firstResult->hasErrors());

        $updates = array_map(fn($category) => $this->makeCategory($category->id), $originals);

        $secondResult = $this->client->importCategories($updates);

        self::assertSame(0, $secondResult->imported);
        self::assertSame(self::BULK_SIZE, $secondResult->updated);
        self::assertFalse($secondResult->hasErrors());

        $expectedNamesById = [];
        foreach ($updates as $category) {
            $expectedNamesById[$category->id] = $category->name->getDefault();
        }

        $verified = 0;
        foreach ($this->client->iterateCategories(isEdited: false, lang: [Language::ALL]) as $category) {
            if (isset($expectedNamesById[$category->id])) {
                self::assertSame(
                    $expectedNamesById[$category->id],
                    $category->name->getDefault(),
                );
                $verified++;
            }
            if ($verified === self::BULK_SIZE) {
                break;
            }
        }

        self::assertSame(self::BULK_SIZE, $verified);
    }

    public function testReimportBrandsTriggersUpdate(): void
    {
        $originals = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $originals[] = $this->makeBrand();
        }

        $firstResult = $this->client->importBrands($originals);

        self::assertSame(self::BULK_SIZE, $firstResult->imported);
        self::assertSame(0, $firstResult->updated);
        self::assertFalse($firstResult->hasErrors());

        $updates = array_map(fn($brand) => $this->makeBrand($brand->id), $originals);

        $secondResult = $this->client->importBrands($updates);

        self::assertSame(0, $secondResult->imported);
        self::assertSame(self::BULK_SIZE, $secondResult->updated);
        self::assertFalse($secondResult->hasErrors());

        $expectedNamesById = [];
        foreach ($updates as $brand) {
            $expectedNamesById[$brand->id] = $brand->name->getDefault();
        }

        $verified = 0;
        foreach ($this->client->iterateBrands(isEdited: false, lang: [Language::ALL]) as $brand) {
            if (isset($expectedNamesById[$brand->id])) {
                self::assertSame(
                    $expectedNamesById[$brand->id],
                    $brand->name->getDefault(),
                );
                $verified++;
            }
            if ($verified === self::BULK_SIZE) {
                break;
            }
        }

        self::assertSame(self::BULK_SIZE, $verified);
    }

    public function testReimportBlogsTriggersUpdate(): void
    {
        $originals = [];
        for ($i = 0; $i < self::BULK_SIZE; $i++) {
            $originals[] = $this->makeBlog();
        }

        $firstResult = $this->client->importBlogs($originals);

        self::assertSame(self::BULK_SIZE, $firstResult->imported);
        self::assertSame(0, $firstResult->updated);
        self::assertFalse($firstResult->hasErrors());

        $updates = array_map(fn($blog) => $this->makeBlog($blog->id), $originals);

        $secondResult = $this->client->importBlogs($updates);

        self::assertSame(0, $secondResult->imported);
        self::assertSame(self::BULK_SIZE, $secondResult->updated);
        self::assertFalse($secondResult->hasErrors());

        $expectedNamesById = [];
        foreach ($updates as $blog) {
            $expectedNamesById[$blog->id] = $blog->name->getDefault();
        }

        $verified = 0;
        foreach ($this->client->iterateBlogs(isEdited: false, lang: [Language::ALL]) as $blog) {
            if (isset($expectedNamesById[$blog->id])) {
                self::assertSame(
                    $expectedNamesById[$blog->id],
                    $blog->name->getDefault(),
                );
                $verified++;
            }
            if ($verified === self::BULK_SIZE) {
                break;
            }
        }

        self::assertSame(self::BULK_SIZE, $verified);
    }
}

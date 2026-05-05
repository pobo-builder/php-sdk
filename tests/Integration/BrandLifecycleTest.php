<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\Language;

final class BrandLifecycleTest extends IntegrationTestCase
{
    public function testImportGetAndDeleteBrand(): void
    {
        $brandId = $this->uniqueId('brand');
        $this->trackBrand($brandId);

        $brand = new Brand(
            id: $brandId,
            isVisible: true,
            name: LocalizedString::create('SDK Integration Test Brand')
                ->withTranslation(Language::CS, 'SDK Integration Test Značka'),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $brandId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $brandId)),
            imagePreview: 'https://example.com/brands/sdk-test-logo.png',
            description: LocalizedString::create('<p>Created by SDK CI</p>')
                ->withTranslation(Language::CS, '<p>Vytvořeno SDK CI</p>'),
        );

        $importResult = $this->client->importBrands([$brand]);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected skipped items: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(1, $importResult->imported + $importResult->updated);

        $found = null;
        foreach ($this->client->iterateBrands(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $brandId) {
                $found = $candidate;
                break;
            }
        }

        self::assertNotNull($found, sprintf('Imported brand %s was not returned by iterateBrands().', $brandId));
        self::assertSame('SDK Integration Test Brand', $found->name->getDefault());
        self::assertSame('https://example.com/brands/sdk-test-logo.png', $found->imagePreview);

        $deleteResult = $this->client->deleteBrands([$brandId]);

        self::assertTrue($deleteResult->success);
        self::assertSame(1, $deleteResult->deleted);
        self::assertFalse($deleteResult->hasErrors());

        $this->untrackBrand($brandId);
    }

    public function testImportProductWithBrandPairing(): void
    {
        $brandId = $this->uniqueId('brand');
        $this->trackBrand($brandId);

        $brand = new Brand(
            id: $brandId,
            isVisible: true,
            name: LocalizedString::create('SDK Brand Pair Test')
                ->withTranslation(Language::CS, 'SDK Brand Pair Test CZ'),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $brandId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $brandId)),
        );

        $brandImport = $this->client->importBrands([$brand]);
        self::assertFalse($brandImport->hasErrors(), sprintf('Brand import failed: %s', json_encode($brandImport->errors)));

        $productId = $this->uniqueId('prod');
        $this->trackProduct($productId);

        $product = new Product(
            id: $productId,
            isVisible: true,
            name: LocalizedString::create('SDK Brand-paired Product')
                ->withTranslation(Language::CS, 'SDK Produkt s brandem'),
            url: LocalizedString::create(sprintf('https://example.com/%s', $productId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $productId)),
            brandId: $brandId,
        );

        $productImport = $this->client->importProducts([$product]);

        self::assertTrue($productImport->success);
        self::assertSame(0, $productImport->skipped, sprintf('Unexpected skipped items: %s', json_encode($productImport->errors)));
        self::assertFalse($productImport->hasErrors());

        $foundProduct = null;
        foreach ($this->client->iterateProducts(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $productId) {
                $foundProduct = $candidate;
                break;
            }
        }

        self::assertNotNull($foundProduct, sprintf('Imported product %s was not returned.', $productId));
        self::assertSame($brandId, $foundProduct->brandId, 'Product should report the assigned brand_id.');

        $unsetProduct = new Product(
            id: $productId,
            isVisible: true,
            name: LocalizedString::create('SDK Brand-paired Product')
                ->withTranslation(Language::CS, 'SDK Produkt s brandem'),
            url: LocalizedString::create(sprintf('https://example.com/%s', $productId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $productId)),
            brandId: null,
        );

        $unsetResult = $this->client->importProducts([$unsetProduct]);
        self::assertFalse($unsetResult->hasErrors(), sprintf('Brand-unset re-import failed: %s', json_encode($unsetResult->errors)));

        $reread = null;
        foreach ($this->client->iterateProducts(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $productId) {
                $reread = $candidate;
                break;
            }
        }

        self::assertNotNull($reread);
        self::assertNull($reread->brandId, 'Sending brand_id: null should clear the product brand assignment.');
    }

    public function testReimportBrandWithoutImagePreviewKeepsExistingLogo(): void
    {
        $brandId = $this->uniqueId('brand');
        $this->trackBrand($brandId);

        $logoUrl = 'https://example.com/brands/sdk-omit-test-logo.png';

        $original = new Brand(
            id: $brandId,
            isVisible: true,
            name: LocalizedString::create('SDK Omit Test')
                ->withTranslation(Language::CS, 'SDK Omit Test CZ'),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $brandId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $brandId)),
            imagePreview: $logoUrl,
        );

        $first = $this->client->importBrands([$original]);
        self::assertFalse($first->hasErrors(), sprintf('Brand import failed: %s', json_encode($first->errors)));

        // Re-import without imagePreview (default sentinel) → server must keep the logo.
        $reimport = new Brand(
            id: $brandId,
            isVisible: true,
            name: LocalizedString::create('SDK Omit Test renamed')
                ->withTranslation(Language::CS, 'SDK Omit Test CZ renamed'),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $brandId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $brandId)),
        );

        $second = $this->client->importBrands([$reimport]);
        self::assertFalse($second->hasErrors(), sprintf('Brand re-import failed: %s', json_encode($second->errors)));

        $found = null;
        foreach ($this->client->iterateBrands(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $brandId) {
                $found = $candidate;
                break;
            }
        }

        self::assertNotNull($found);
        self::assertSame($logoUrl, $found->imagePreview, 'Omitting imagePreview must preserve the existing logo, not clear it.');
        self::assertSame('SDK Omit Test renamed', $found->name->getDefault(), 'Other fields should still update.');

        // Now explicitly clear the logo.
        $clear = new Brand(
            id: $brandId,
            isVisible: true,
            name: LocalizedString::create('SDK Omit Test renamed')
                ->withTranslation(Language::CS, 'SDK Omit Test CZ renamed'),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $brandId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $brandId)),
            imagePreview: null,
        );

        $third = $this->client->importBrands([$clear]);
        self::assertFalse($third->hasErrors(), sprintf('Brand clear-logo import failed: %s', json_encode($third->errors)));

        $reread = null;
        foreach ($this->client->iterateBrands(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $brandId) {
                $reread = $candidate;
                break;
            }
        }

        self::assertNotNull($reread);
        self::assertNull($reread->imagePreview, 'Sending imagePreview: null should clear the logo.');
    }

    public function testImportProductWithInvalidBrandIdReportsError(): void
    {
        $productId = $this->uniqueId('prod');
        $this->trackProduct($productId);

        $product = new Product(
            id: $productId,
            isVisible: true,
            name: LocalizedString::create('SDK Invalid Brand Product')
                ->withTranslation(Language::CS, 'SDK Produkt'),
            url: LocalizedString::create(sprintf('https://example.com/%s', $productId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $productId)),
            brandId: $this->uniqueId('nonexistent-brand'),
        );

        $result = $this->client->importProducts([$product]);

        self::assertSame(1, $result->skipped, 'Product with invalid brand_id should be skipped.');
        self::assertTrue($result->hasErrors(), 'Invalid brand_id should produce a per-item error.');
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\Language;

final class ProductLifecycleTest extends IntegrationTestCase
{
    public function testImportGetAndDeleteProduct(): void
    {
        $productId = $this->uniqueId('prod');
        $this->trackProduct($productId);

        $product = new Product(
            id: $productId,
            isVisible: true,
            name: LocalizedString::create('SDK Integration Test Product')
                ->withTranslation(Language::CS, 'SDK Integration Test Produkt'),
            url: LocalizedString::create(sprintf('https://example.com/%s', $productId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $productId)),
            shortDescription: LocalizedString::create('Created by SDK CI'),
        );

        $importResult = $this->client->importProducts([$product]);

        self::assertTrue($importResult->success, 'Import should report success.');
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected skipped items: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors(), 'Import should not produce per-item errors.');
        self::assertSame(1, $importResult->imported + $importResult->updated, 'Exactly one item should be imported or updated.');

        $found = null;
        foreach ($this->client->iterateProducts(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $productId) {
                $found = $candidate;
                break;
            }
        }

        self::assertNotNull($found, sprintf('Imported product %s was not returned by iterateProducts().', $productId));
        self::assertSame('SDK Integration Test Product', $found->name->getDefault());

        $deleteResult = $this->client->deleteProducts([$productId]);

        self::assertTrue($deleteResult->success);
        self::assertSame(1, $deleteResult->deleted);
        self::assertFalse($deleteResult->hasErrors());

        $this->untrackProduct($productId);
    }
}

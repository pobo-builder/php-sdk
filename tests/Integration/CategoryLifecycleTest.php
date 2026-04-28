<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

final class CategoryLifecycleTest extends IntegrationTestCase
{
    public function testImportGetAndDeleteCategory(): void
    {
        $categoryId = $this->uniqueId('cat');
        $this->trackCategory($categoryId);

        $category = new Category(
            id: $categoryId,
            isVisible: true,
            name: LocalizedString::create('SDK Integration Test Category')
                ->withTranslation(Language::CS, 'SDK Integration Test Kategorie'),
            url: LocalizedString::create(sprintf('https://example.com/%s', $categoryId))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $categoryId)),
            description: LocalizedString::create('<p>Created by SDK CI</p>')
                ->withTranslation(Language::CS, '<p>Vytvořeno SDK CI</p>'),
        );

        $importResult = $this->client->importCategories([$category]);

        self::assertTrue($importResult->success);
        self::assertSame(0, $importResult->skipped, sprintf('Unexpected skipped items: %s', json_encode($importResult->errors)));
        self::assertFalse($importResult->hasErrors());
        self::assertSame(1, $importResult->imported + $importResult->updated);

        $found = null;
        foreach ($this->client->iterateCategories(isEdited: false, lang: [Language::ALL]) as $candidate) {
            if ($candidate->id === $categoryId) {
                $found = $candidate;
                break;
            }
        }

        self::assertNotNull($found, sprintf('Imported category %s was not returned by iterateCategories().', $categoryId));
        self::assertSame('SDK Integration Test Category', $found->name->getDefault());

        $deleteResult = $this->client->deleteCategories([$categoryId]);

        self::assertTrue($deleteResult->success);
        self::assertSame(1, $deleteResult->deleted);
        self::assertFalse($deleteResult->hasErrors());

        $this->untrackCategory($categoryId);
    }
}

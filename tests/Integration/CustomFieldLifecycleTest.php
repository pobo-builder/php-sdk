<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Pobo\Sdk\DTO\CustomField;
use Pobo\Sdk\DTO\CustomFieldAssignment;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\IncludeContent;
use Pobo\Sdk\PoboClient;

/**
 * Custom fields exist only on b2b eshops, so this test needs its own token:
 * POBO_B2B_API_TOKEN (and optionally POBO_BASE_URL). Without it the test is skipped.
 */
final class CustomFieldLifecycleTest extends IntegrationTestCase
{
    private string $code;

    protected function setUp(): void
    {
        parent::setUp();

        $token = getenv('POBO_B2B_API_TOKEN');
        if ($token === false || $token === '') {
            self::markTestSkipped('Custom field tests require POBO_B2B_API_TOKEN of a b2b eshop.');
        }

        $baseUrl = getenv('POBO_BASE_URL');
        $this->client = new PoboClient(
            apiToken: $token,
            baseUrl: $baseUrl === false || $baseUrl === '' ? 'https://api.pobo.space' : $baseUrl,
            timeout: 60,
        );

        $this->code = strtolower(str_replace('-', '_', $this->uniqueId('field')));
    }

    protected function tearDown(): void
    {
        try {
            $this->client->deleteCustomFields([$this->code]);
        } catch (\Throwable) {
            // Cleanup is best-effort; don't mask the actual test failure.
        }

        parent::tearDown();
    }

    public function testDefineAssignFilterAndDelete(): void
    {
        $import = $this->client->importCustomFields([
            new CustomField(code: $this->code, name: 'SDK CI segment', values: ['wholesale', 'retail']),
        ]);
        self::assertFalse($import->hasErrors(), sprintf('Custom field import failed: %s', json_encode($import->errors)));

        $field = $this->findField();
        self::assertNotNull($field, 'The imported field was not returned by getCustomFields().');
        self::assertEqualsCanonicalizing(['wholesale', 'retail'], $field->values);

        $matching = $this->withCustomFields(product: $this->makeProduct(), values: ['wholesale', 'vip']);
        $other = $this->withCustomFields(product: $this->makeProduct(), values: ['retail']);

        $productImport = $this->client->importProducts([$matching, $other]);
        self::assertSame(0, $productImport->skipped, sprintf('Unexpected skipped products: %s', json_encode($productImport->errors)));

        $found = [];
        foreach ($this->client->iterateProducts(
            isEdited: false,
            include: [IncludeContent::CUSTOM_FIELD],
            customField: [$this->code => ['vip']],
        ) as $product) {
            $found[$product->id] = $product;
        }

        self::assertSame([$matching->id], array_keys($found), 'The filter should return only the product carrying "vip".');
        $assigned = $found[$matching->id]->customFields ?? [];
        self::assertCount(1, $assigned);
        self::assertSame($this->code, $assigned[0]->code);
        self::assertSame('SDK CI segment', $assigned[0]->name);
        self::assertEqualsCanonicalizing(['wholesale', 'vip'], $assigned[0]->values);

        $valueDelete = $this->client->deleteCustomFieldValues([$this->code => ['vip']]);
        self::assertSame(1, $valueDelete->deleted);

        $afterValueDelete = iterator_to_array($this->client->iterateProducts(
            isEdited: false,
            customField: [$this->code => ['vip']],
        ), false);
        self::assertSame([], $afterValueDelete, 'No product should carry a deleted value.');

        $fieldDelete = $this->client->deleteCustomFields([$this->code]);
        self::assertSame(1, $fieldDelete->deleted);
        self::assertNull($this->findField());
    }

    private function findField(): ?CustomField
    {
        $page = 1;

        do {
            $response = $this->client->getCustomFields(page: $page, perPage: 100);
            foreach ($response->data as $field) {
                if ($field->code === $this->code) {
                    return $field;
                }
            }
            $page++;
        } while ($response->hasMorePages());

        return null;
    }

    /**
     * @param array<string> $values
     */
    private function withCustomFields(Product $product, array $values): Product
    {
        return new Product(
            id: $product->id,
            isVisible: $product->isVisible,
            name: $product->name,
            url: $product->url,
            shortDescription: $product->shortDescription,
            description: $product->description,
            customFields: [new CustomFieldAssignment(code: $this->code, values: $values)],
        );
    }
}

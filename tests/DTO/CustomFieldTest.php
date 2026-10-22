<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\CustomField;
use Pobo\Sdk\DTO\CustomFieldAssignment;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;

final class CustomFieldTest extends TestCase
{
    public function testCustomFieldToArray(): void
    {
        $field = new CustomField(code: 'segment', name: 'Segment', values: ['wholesale', 'vip']);

        $this->assertSame(
            ['code' => 'segment', 'name' => 'Segment', 'value' => ['wholesale', 'vip']],
            $field->toArray(),
        );
    }

    public function testCustomFieldFromArray(): void
    {
        $field = CustomField::fromArray([
            'code' => 'segment',
            'name' => 'Segment',
            'value' => ['retail', 'vip'],
            'created_at' => '2026-10-22T10:30:00.000000Z',
            'updated_at' => '2026-10-22T11:00:00.000000Z',
        ]);

        $this->assertSame('segment', $field->code);
        $this->assertSame(['retail', 'vip'], $field->values);
        $this->assertSame('2026-10-22', $field->createdAt?->format('Y-m-d'));
    }

    public function testProductWithoutCustomFieldsDoesNotSendTheKey(): void
    {
        $this->assertArrayNotHasKey('custom_field', $this->product(customFields: null)->toArray());
    }

    public function testProductWithEmptyCustomFieldsSendsEmptyListToRemoveAll(): void
    {
        $this->assertSame([], $this->product(customFields: [])->toArray()['custom_field']);
    }

    public function testProductSendsCustomFields(): void
    {
        $product = $this->product(customFields: [
            new CustomFieldAssignment(code: 'segment', values: ['wholesale', 'vip']),
        ]);

        $this->assertSame(
            [['code' => 'segment', 'value' => ['wholesale', 'vip']]],
            $product->toArray()['custom_field'],
        );
    }

    public function testProductReadsCustomFieldsFromInclude(): void
    {
        $product = Product::fromArray([
            'id' => 'P-1',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com/p-1'],
            'custom_field' => [['code' => 'segment', 'name' => 'Segment', 'value' => ['vip']]],
        ]);

        $this->assertNotNull($product->customFields);
        $this->assertSame('segment', $product->customFields[0]->code);
        $this->assertSame('Segment', $product->customFields[0]->name);
        $this->assertSame(['vip'], $product->customFields[0]->values);
    }

    public function testProductWithoutIncludeHasNullCustomFields(): void
    {
        $product = Product::fromArray([
            'id' => 'P-1',
            'is_visible' => true,
            'name' => ['default' => 'Product'],
            'url' => ['default' => 'https://example.com/p-1'],
        ]);

        $this->assertNull($product->customFields);
    }

    public function testCategorySendsAndReadsCustomFields(): void
    {
        $category = new Category(
            id: 'C-1',
            isVisible: true,
            name: LocalizedString::create('Category'),
            url: LocalizedString::create('https://example.com/c-1'),
            customFields: [new CustomFieldAssignment(code: 'segment', values: ['vip'])],
        );

        $data = $category->toArray();
        $this->assertSame([['code' => 'segment', 'value' => ['vip']]], $data['custom_field']);

        $read = Category::fromArray($data);
        $this->assertSame(['vip'], $read->customFields[0]->values ?? null);
    }

    /**
     * @param array<CustomFieldAssignment>|null $customFields
     */
    private function product(?array $customFields): Product
    {
        return new Product(
            id: 'P-1',
            isVisible: true,
            name: LocalizedString::create('Product'),
            url: LocalizedString::create('https://example.com/p-1'),
            customFields: $customFields,
        );
    }
}

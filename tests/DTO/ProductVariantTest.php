<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\ProductVariant;

final class ProductVariantTest extends TestCase
{
    public function testFromArray(): void
    {
        $variant = ProductVariant::fromArray([
            'code' => 'ABC-001',
            'ean' => '8591234567890',
        ]);

        $this->assertSame('ABC-001', $variant->code);
        $this->assertSame('8591234567890', $variant->ean);
    }

    public function testFromArrayWithNullEan(): void
    {
        $variant = ProductVariant::fromArray([
            'code' => 'ABC-002',
            'ean' => null,
        ]);

        $this->assertSame('ABC-002', $variant->code);
        $this->assertNull($variant->ean);
    }

    public function testFromArrayWithMissingEan(): void
    {
        $variant = ProductVariant::fromArray([
            'code' => 'ABC-003',
        ]);

        $this->assertSame('ABC-003', $variant->code);
        $this->assertNull($variant->ean);
    }

    public function testToArray(): void
    {
        $variant = new ProductVariant(
            code: 'ABC-001',
            ean: '8591234567890',
        );

        $this->assertSame([
            'code' => 'ABC-001',
            'ean' => '8591234567890',
        ], $variant->toArray());
    }

    public function testRoundtrip(): void
    {
        $data = ['code' => 'ABC-001', 'ean' => '8591234567890'];
        $variant = ProductVariant::fromArray($data);

        $this->assertSame($data, $variant->toArray());
    }
}

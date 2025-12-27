<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\ImportResult;

final class ImportResultTest extends TestCase
{
    public function testFromArraySuccess(): void
    {
        $data = [
            'success' => true,
            'imported' => 5,
            'updated' => 3,
            'skipped' => 1,
            'errors' => [],
        ];

        $result = ImportResult::fromArray($data);

        $this->assertTrue($result->success);
        $this->assertSame(5, $result->imported);
        $this->assertSame(3, $result->updated);
        $this->assertSame(1, $result->skipped);
        $this->assertSame([], $result->errors);
        $this->assertFalse($result->hasErrors());
    }

    public function testFromArrayWithErrors(): void
    {
        $data = [
            'success' => true,
            'imported' => 4,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [
                [
                    'index' => 5,
                    'id' => 'PROD-005',
                    'errors' => ['The name.default field is required.'],
                ],
            ],
        ];

        $result = ImportResult::fromArray($data);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertSame(5, $result->errors[0]['index']);
        $this->assertSame('PROD-005', $result->errors[0]['id']);
    }

    public function testFromArrayWithParameterValues(): void
    {
        $data = [
            'success' => true,
            'imported' => 2,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'values_imported' => 10,
            'values_updated' => 5,
        ];

        $result = ImportResult::fromArray($data);

        $this->assertSame(10, $result->valuesImported);
        $this->assertSame(5, $result->valuesUpdated);
    }

    public function testFromArrayWithDefaults(): void
    {
        $result = ImportResult::fromArray([]);

        $this->assertFalse($result->success);
        $this->assertSame(0, $result->imported);
        $this->assertSame(0, $result->updated);
        $this->assertSame(0, $result->skipped);
        $this->assertSame([], $result->errors);
        $this->assertNull($result->valuesImported);
        $this->assertNull($result->valuesUpdated);
    }

    public function testHasErrorsReturnsFalseForEmptyErrors(): void
    {
        $result = new ImportResult(
            success: true,
            imported: 1,
            updated: 0,
            skipped: 0,
            errors: [],
        );

        $this->assertFalse($result->hasErrors());
    }

    public function testHasErrorsReturnsTrueForNonEmptyErrors(): void
    {
        $result = new ImportResult(
            success: true,
            imported: 0,
            updated: 0,
            skipped: 1,
            errors: [['index' => 0, 'id' => 'X', 'errors' => ['Error']]],
        );

        $this->assertTrue($result->hasErrors());
    }
}

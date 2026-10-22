<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\MultiImportResult;

final class MultiImportResultTest extends TestCase
{
    public function testFromArray(): void
    {
        $result = MultiImportResult::fromArray([
            'success' => true,
            'results' => [
                'custom_fields' => ['imported' => 1, 'updated' => 0, 'skipped' => 0, 'errors' => []],
                'products' => ['imported' => 0, 'updated' => 10, 'skipped' => 0, 'errors' => []],
            ],
        ]);

        $this->assertTrue($result->success);
        $this->assertSame(1, $result->section(MultiImportResult::SECTION_CUSTOM_FIELDS)->imported);
        $this->assertSame(10, $result->section(MultiImportResult::SECTION_PRODUCTS)->updated);
        $this->assertTrue($result->section(MultiImportResult::SECTION_PRODUCTS)->success);
    }

    public function testMissingSectionReadsAsNothingImported(): void
    {
        $result = MultiImportResult::fromArray(['success' => true, 'results' => []]);

        $section = $result->section(MultiImportResult::SECTION_BLOGS);

        $this->assertSame(0, $section->imported);
        $this->assertSame(0, $section->updated);
        $this->assertFalse($section->hasErrors());
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\DeleteResult;

final class DeleteResultTest extends TestCase
{
    public function testFromArraySuccess(): void
    {
        $data = [
            'success' => true,
            'deleted' => 3,
            'skipped' => 1,
            'errors' => [],
        ];

        $result = DeleteResult::fromArray($data);

        $this->assertTrue($result->success);
        $this->assertSame(3, $result->deleted);
        $this->assertSame(1, $result->skipped);
        $this->assertSame([], $result->errors);
        $this->assertFalse($result->hasErrors());
    }

    public function testFromArrayWithErrors(): void
    {
        $data = [
            'success' => true,
            'deleted' => 2,
            'skipped' => 1,
            'errors' => [
                [
                    'index' => 2,
                    'id' => 'PROD-999',
                    'errors' => ['Product not found'],
                ],
            ],
        ];

        $result = DeleteResult::fromArray($data);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertSame(2, $result->errors[0]['index']);
        $this->assertSame('PROD-999', $result->errors[0]['id']);
    }

    public function testFromArrayWithDefaults(): void
    {
        $result = DeleteResult::fromArray([]);

        $this->assertFalse($result->success);
        $this->assertSame(0, $result->deleted);
        $this->assertSame(0, $result->skipped);
        $this->assertSame([], $result->errors);
    }

    public function testHasErrorsReturnsFalseForEmptyErrors(): void
    {
        $result = new DeleteResult(
            success: true,
            deleted: 1,
            skipped: 0,
            errors: [],
        );

        $this->assertFalse($result->hasErrors());
    }

    public function testHasErrorsReturnsTrueForNonEmptyErrors(): void
    {
        $result = new DeleteResult(
            success: true,
            deleted: 0,
            skipped: 1,
            errors: [['index' => 0, 'id' => 'X', 'errors' => ['Not found']]],
        );

        $this->assertTrue($result->hasErrors());
    }
}

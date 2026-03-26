<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Exception\ValidationException;

final class ValidationExceptionTest extends TestCase
{
    public function testEmptyPayload(): void
    {
        $exception = ValidationException::emptyPayload();

        $this->assertSame('Payload cannot be empty', $exception->getMessage());
        $this->assertArrayHasKey('bulk', $exception->errors);
        $this->assertContains('At least one item is required', $exception->errors['bulk']);
    }

    public function testTooManyItems(): void
    {
        $exception = ValidationException::tooManyItems(150, 100);

        $this->assertSame('Too many items: 150 provided, maximum is 100', $exception->getMessage());
        $this->assertArrayHasKey('bulk', $exception->errors);
        $this->assertStringContainsString('Maximum 100 items', $exception->errors['bulk'][0]);
    }

    public function testCustomErrors(): void
    {
        $errors = ['field' => ['Error 1', 'Error 2']];
        $exception = new ValidationException('Validation failed', $errors);

        $this->assertSame('Validation failed', $exception->getMessage());
        $this->assertSame($errors, $exception->errors);
    }

    public function testDefaultEmptyErrors(): void
    {
        $exception = new ValidationException('Some error');

        $this->assertSame([], $exception->errors);
    }
}

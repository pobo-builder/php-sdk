<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class DeleteResult
{
    /**
     * @param array<array{index: int, id: string, errors: array<string>}> $errors
     */
    public function __construct(
        public readonly bool $success,
        public readonly int $deleted,
        public readonly int $skipped,
        public readonly array $errors = [],
    ) {
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            deleted: $data['deleted'] ?? 0,
            skipped: $data['skipped'] ?? 0,
            errors: $data['errors'] ?? [],
        );
    }
}

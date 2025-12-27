<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class ImportResult
{
    /**
     * @param array<array{index: int, id: string, errors: array<string>}> $errors
     */
    public function __construct(
        public readonly bool $success,
        public readonly int $imported,
        public readonly int $updated,
        public readonly int $skipped,
        public readonly array $errors = [],
        public readonly ?int $valuesImported = null,
        public readonly ?int $valuesUpdated = null,
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
            imported: $data['imported'] ?? 0,
            updated: $data['updated'] ?? 0,
            skipped: $data['skipped'] ?? 0,
            errors: $data['errors'] ?? [],
            valuesImported: $data['values_imported'] ?? null,
            valuesUpdated: $data['values_updated'] ?? null,
        );
    }
}

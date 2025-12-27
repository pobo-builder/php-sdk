<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

readonly class ImportResult
{
    /**
     * @param array<array{index: int, id: string, errors: array<string>}> $errors
     */
    public function __construct(
        public bool $success,
        public int $imported,
        public int $updated,
        public int $skipped,
        public array $errors = [],
        public ?int $valuesImported = null,
        public ?int $valuesUpdated = null,
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

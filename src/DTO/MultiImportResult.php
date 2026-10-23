<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * Result of POST /api/v2/rest/multi-import. The import is all-or-nothing: any invalid item
 * makes the API answer 422 (thrown as ApiException) and nothing is written, so a result
 * here always means every section went through.
 */
final class MultiImportResult
{
    public const SECTION_CUSTOM_FIELDS = 'custom_fields';
    public const SECTION_PARAMETERS = 'parameters';
    public const SECTION_CATEGORIES = 'categories';
    public const SECTION_BRANDS = 'brands';
    public const SECTION_PRODUCTS = 'products';
    public const SECTION_BLOGS = 'blogs';

    /**
     * @param array<string, ImportResult> $results keyed by section
     */
    public function __construct(
        public readonly bool $success,
        public readonly array $results,
    ) {
    }

    /**
     * Result of one section; a section the response does not carry reads as nothing imported.
     */
    public function section(string $section): ImportResult
    {
        return $this->results[$section] ?? new ImportResult(success: $this->success, imported: 0, updated: 0, skipped: 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array{success?: bool, results?: array<string, array<string, mixed>>} $data */
        $success = $data['success'] ?? false;

        $results = [];
        foreach ($data['results'] ?? [] as $section => $result) {
            $results[$section] = ImportResult::fromArray(['success' => $success] + $result);
        }

        return new self(success: $success, results: $results);
    }
}

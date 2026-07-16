<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * Single product variant returned by GET /api/v2/rest/products?include=variant.
 *
 * Variants are read-only in the API — they come from the platform import,
 * ordered by internal ID. `ean` is nullable (returned as stored).
 */
final class ProductVariant
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $ean = null,
    ) {
    }

    /**
     * @return array{code: string, ean: string|null}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'ean' => $this->ean,
        ];
    }

    /**
     * @param array{code: string, ean?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            ean: $data['ean'] ?? null,
        );
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class PaginatedResponse
{
    /**
     * @param array<Product|Category> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
    ) {
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage * $this->perPage < $this->total;
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * @param array<string, mixed> $response
     * @param class-string<Product|Category> $entityClass
     */
    public static function fromArray(array $response, string $entityClass): self
    {
        $data = array_map(
            fn(array $item) => $entityClass::fromArray($item),
            $response['data'] ?? []
        );

        $meta = $response['meta'] ?? [];

        return new self(
            data: $data,
            currentPage: $meta['current_page'] ?? 1,
            perPage: $meta['per_page'] ?? 100,
            total: $meta['total'] ?? count($data),
        );
    }
}

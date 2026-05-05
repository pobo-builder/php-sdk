<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * @template T of Product|Category|Blog|Brand
 */
final class PaginatedResponse
{
    /**
     * @param array<T> $data
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
     * @template TEntity of Product|Category|Blog|Brand
     * @param array<string, mixed> $response
     * @param class-string<TEntity> $entityClass
     * @return self<TEntity>
     */
    public static function fromArray(array $response, string $entityClass): self
    {
        /** @var array<array<string, mixed>> $responseData */
        $responseData = $response['data'] ?? [];

        $data = array_map(
            fn(array $item) => $entityClass::fromArray($item),
            $responseData,
        );

        /** @var array{current_page?: int, per_page?: int, total?: int} $meta */
        $meta = $response['meta'] ?? [];

        /** @var self<TEntity> $result */
        $result = new self(
            data: $data,
            currentPage: $meta['current_page'] ?? 1,
            perPage: $meta['per_page'] ?? 100,
            total: $meta['total'] ?? count($data),
        );

        return $result;
    }
}

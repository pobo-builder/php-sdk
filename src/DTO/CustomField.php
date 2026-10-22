<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * A custom filtering field of the eshop (b2b eshops only), as sent to and read from
 * /api/v2/rest/custom-fields. `code` is the stable key used in imports and filters;
 * `values` are only added on import — remove one with PoboClient::deleteCustomFieldValues().
 */
final class CustomField
{
    /**
     * @param array<string> $values
     */
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly array $values = [],
        public readonly ?\DateTimeInterface $createdAt = null,
        public readonly ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    /**
     * @return array{code: string, name: string, value: array<string>}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'value' => array_values($this->values),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array{code: string, name: string, value?: array<string>, created_at?: string|null, updated_at?: string|null} $data */
        return new self(
            code: $data['code'],
            name: $data['name'],
            values: $data['value'] ?? [],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }
}

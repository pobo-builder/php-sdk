<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * Values of one custom field on a product or category. On import only `code` and `values`
 * are sent; `name` is filled when read back with IncludeContent::CUSTOM_FIELD.
 */
final class CustomFieldAssignment
{
    /**
     * @param array<string> $values
     */
    public function __construct(
        public readonly string $code,
        public readonly array $values,
        public readonly ?string $name = null,
    ) {
    }

    /**
     * @return array{code: string, value: array<string>}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'value' => array_values($this->values),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array{code: string, value?: array<string>, name?: string|null} $data */
        return new self(
            code: $data['code'],
            values: $data['value'] ?? [],
            name: $data['name'] ?? null,
        );
    }

    /**
     * @param array<self|array<string, mixed>> $customFields
     * @return array<array{code: string, value: array<string>}|array<string, mixed>>
     */
    public static function toPayload(array $customFields): array
    {
        return array_values(array_map(
            static fn(self|array $item): array => $item instanceof self ? $item->toArray() : $item,
            $customFields,
        ));
    }

    /**
     * @param array<array<string, mixed>> $data
     * @return array<self>
     */
    public static function listFromArray(array $data): array
    {
        return array_map(static fn(array $item): self => self::fromArray($item), $data);
    }
}

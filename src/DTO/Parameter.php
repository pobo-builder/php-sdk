<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

readonly class Parameter
{
    /**
     * @param array<ParameterValue> $values
     */
    public function __construct(
        public int $id,
        public string $name,
        public array $values,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'values' => array_map(fn(ParameterValue $v) => $v->toArray(), $this->values),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            values: array_map(fn(array $v) => ParameterValue::fromArray($v), $data['values'] ?? []),
        );
    }
}

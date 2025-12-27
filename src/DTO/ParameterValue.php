<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

readonly class ParameterValue
{
    public function __construct(
        public int $id,
        public string $value,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            value: $data['value'],
        );
    }
}

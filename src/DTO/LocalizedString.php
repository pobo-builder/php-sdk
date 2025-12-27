<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\Language;

readonly class LocalizedString
{
    /**
     * @param array<string, string|null> $values
     */
    public function __construct(
        private array $values,
    ) {
    }

    public static function create(string $default): self
    {
        return new self([Language::DEFAULT->value => $default]);
    }

    public function withTranslation(Language $language, ?string $value): self
    {
        $values = $this->values;
        $values[$language->value] = $value;
        return new self($values);
    }

    public function get(Language $language): ?string
    {
        return $this->values[$language->value] ?? null;
    }

    public function getDefault(): ?string
    {
        return $this->get(Language::DEFAULT);
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * @param array<string, string|null> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}

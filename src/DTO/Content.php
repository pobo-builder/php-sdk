<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\Language;

final class Content
{
    /**
     * @param array<string, string> $html
     * @param array<string, string> $marketplace
     * @param array<int, array<mixed>> $nested
     */
    public function __construct(
        public readonly array $html = [],
        public readonly array $marketplace = [],
        public readonly array $nested = [],
    ) {
    }

    public function getHtml(Language $language): ?string
    {
        return $this->html[$language->value] ?? null;
    }

    public function getMarketplace(Language $language): ?string
    {
        return $this->marketplace[$language->value] ?? null;
    }

    public function getHtmlDefault(): ?string
    {
        return $this->html['default'] ?? $this->html['cs'] ?? null;
    }

    public function getMarketplaceDefault(): ?string
    {
        return $this->marketplace['default'] ?? $this->marketplace['cs'] ?? null;
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function getNested(): array
    {
        return $this->nested;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'html' => $this->html,
            'marketplace' => $this->marketplace,
        ];

        if ($this->nested !== []) {
            $result['nested'] = $this->nested;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array{html?: array<string, string>, marketplace?: array<string, string>, nested?: array<int, array<mixed>>} $data */
        return new self(
            html: $data['html'] ?? [],
            marketplace: $data['marketplace'] ?? [],
            nested: $data['nested'] ?? [],
        );
    }
}

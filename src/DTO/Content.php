<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\Language;

final class Content
{
    /**
     * @param array<string, string> $html
     * @param array<string, string> $marketplace
     */
    public function __construct(
        public readonly array $html = [],
        public readonly array $marketplace = [],
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
     * @return array<string, array<string, string>>
     */
    public function toArray(): array
    {
        return [
            'html' => $this->html,
            'marketplace' => $this->marketplace,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            html: $data['html'] ?? [],
            marketplace: $data['marketplace'] ?? [],
        );
    }
}

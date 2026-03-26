<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\Language;

final class RichSnippet
{
    /**
     * @param array<string, string> $html
     * @param array<string, mixed> $json
     */
    public function __construct(
        public readonly array $html = [],
        public readonly array $json = [],
    ) {
    }

    public function getHtml(Language $language): ?string
    {
        return $this->html[$language->value] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getJson(Language $language): ?array
    {
        $value = $this->json[$language->value] ?? null;

        return is_array($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            html: $data['html'] ?? [],
            json: $data['json'] ?? [],
        );
    }
}

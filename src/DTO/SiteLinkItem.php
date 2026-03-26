<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class SiteLinkItem
{
    public function __construct(
        public readonly string $heading,
        public readonly string $slug,
    ) {
    }

    /**
     * @return array{heading: string, slug: string}
     */
    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'slug' => $this->slug,
        ];
    }

    /**
     * @param array{heading: string, slug: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            heading: $data['heading'],
            slug: $data['slug'],
        );
    }
}

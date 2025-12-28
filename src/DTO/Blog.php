<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class Blog
{
    /**
     * @param array<string> $images
     */
    public function __construct(
        public readonly string $id,
        public readonly bool $isVisible,
        public readonly LocalizedString $name,
        public readonly LocalizedString $url,
        public readonly ?string $category = null,
        public readonly ?LocalizedString $description = null,
        public readonly ?LocalizedString $seoTitle = null,
        public readonly ?LocalizedString $seoDescription = null,
        public readonly ?Content $content = null,
        public readonly array $images = [],
        public readonly ?bool $isLoaded = null,
        public readonly ?\DateTimeInterface $createdAt = null,
        public readonly ?\DateTimeInterface $updatedAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'is_visible' => $this->isVisible,
            'name' => $this->name->toArray(),
            'url' => $this->url->toArray(),
        ];

        if ($this->category !== null) {
            $data['category'] = $this->category;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        if ($this->seoTitle !== null) {
            $data['seo_title'] = $this->seoTitle->toArray();
        }

        if ($this->seoDescription !== null) {
            $data['seo_description'] = $this->seoDescription->toArray();
        }

        if ($this->images !== []) {
            $data['images'] = $this->images;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            isVisible: $data['is_visible'],
            name: LocalizedString::fromArray($data['name']),
            url: LocalizedString::fromArray($data['url']),
            category: $data['category'] ?? null,
            description: isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            seoTitle: isset($data['seo_title']) ? LocalizedString::fromArray($data['seo_title']) : null,
            seoDescription: isset($data['seo_description']) ? LocalizedString::fromArray($data['seo_description']) : null,
            content: isset($data['content']) ? Content::fromArray($data['content']) : null,
            images: $data['images'] ?? [],
            isLoaded: $data['is_loaded'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }
}

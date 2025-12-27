<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

final class Blog
{
    /**
     * @param array<string> $images
     */
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $guid = null,
        public readonly ?string $category = null,
        public readonly bool $isVisible = true,
        public readonly ?LocalizedString $name = null,
        public readonly ?LocalizedString $url = null,
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
            'is_visible' => $this->isVisible,
        ];

        if ($this->guid !== null) {
            $data['guid'] = $this->guid;
        }

        if ($this->category !== null) {
            $data['category'] = $this->category;
        }

        if ($this->name !== null) {
            $data['name'] = $this->name->toArray();
        }

        if ($this->url !== null) {
            $data['url'] = $this->url->toArray();
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
            id: $data['id'] ?? null,
            guid: $data['guid'] ?? null,
            category: $data['category'] ?? null,
            isVisible: $data['is_visible'] ?? true,
            name: isset($data['name']) ? LocalizedString::fromArray($data['name']) : null,
            url: isset($data['url']) ? LocalizedString::fromArray($data['url']) : null,
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

<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

readonly class Product
{
    /**
     * @param array<string> $images
     * @param array<string> $categoriesIds
     * @param array<int> $parametersIds
     * @param array<array{id: string, name: array<string, string>}> $categories
     */
    public function __construct(
        public string $id,
        public bool $isVisible,
        public LocalizedString $name,
        public LocalizedString $url,
        public ?LocalizedString $shortDescription = null,
        public ?LocalizedString $description = null,
        public ?LocalizedString $seoTitle = null,
        public ?LocalizedString $seoDescription = null,
        public array $images = [],
        public array $categoriesIds = [],
        public array $parametersIds = [],
        public ?string $guid = null,
        public ?bool $isLoaded = null,
        public array $categories = [],
        public ?\DateTimeInterface $createdAt = null,
        public ?\DateTimeInterface $updatedAt = null,
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

        if ($this->shortDescription !== null) {
            $data['short_description'] = $this->shortDescription->toArray();
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

        if ($this->categoriesIds !== []) {
            $data['categories_ids'] = $this->categoriesIds;
        }

        if ($this->parametersIds !== []) {
            $data['parameters_ids'] = $this->parametersIds;
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
            shortDescription: isset($data['short_description']) ? LocalizedString::fromArray($data['short_description']) : null,
            description: isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            seoTitle: isset($data['seo_title']) ? LocalizedString::fromArray($data['seo_title']) : null,
            seoDescription: isset($data['seo_description']) ? LocalizedString::fromArray($data['seo_description']) : null,
            images: $data['images'] ?? [],
            categoriesIds: $data['categories_ids'] ?? [],
            parametersIds: $data['parameters_ids'] ?? [],
            guid: $data['guid'] ?? null,
            isLoaded: $data['is_loaded'] ?? null,
            categories: $data['categories'] ?? [],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }
}

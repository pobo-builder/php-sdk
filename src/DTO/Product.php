<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

/**
 * @phpstan-type ProductData array{
 *     id: string,
 *     is_visible: bool,
 *     name: array<string, string|null>,
 *     url: array<string, string|null>,
 *     short_description?: array<string, string|null>,
 *     description?: array<string, string|null>,
 *     seo_title?: array<string, string|null>,
 *     seo_description?: array<string, string|null>,
 *     content?: array<string, mixed>,
 *     site_link?: array<string, mixed>,
 *     rich_snippet?: array<string, mixed>,
 *     variant?: array<array{code: string, ean?: string|null}>,
 *     images?: array<string>,
 *     categories_ids?: array<string>,
 *     parameters_ids?: array<int>,
 *     brand_id?: string|null,
 *     guid?: string|null,
 *     is_loaded?: bool,
 *     categories?: array<array{id: string, name: array<string, string>}>,
 *     created_at?: string,
 *     updated_at?: string,
 * }
 */
final class Product
{
    /**
     * Sentinel meaning "do not send brand_id in the import payload" — the server
     * will leave product.brand_id unchanged. Distinct from passing null, which
     * explicitly clears the brand assignment in the DB.
     */
    public const BRAND_ID_UNSET = "\x00POBO_BRAND_ID_UNSET\x00";

    /** @var array<string> */
    public readonly array $categoriesIds;

    /** @var array<int> */
    public readonly array $parametersIds;

    /**
     * @param array<string> $images
     * @param array<string|int|null> $categoriesIds Empty strings and nulls are filtered defensively
     * @param array<int|string|null> $parametersIds Empty strings and nulls are filtered defensively
     * @param array<array{id: string, name: array<string, string>}> $categories
     * @param array<ProductVariant>|null $variants Null when `variant` was not requested via include, empty array when the product has no variants
     */
    public function __construct(
        public readonly string $id,
        public readonly bool $isVisible,
        public readonly LocalizedString $name,
        public readonly LocalizedString $url,
        public readonly ?LocalizedString $shortDescription = null,
        public readonly ?LocalizedString $description = null,
        public readonly ?LocalizedString $seoTitle = null,
        public readonly ?LocalizedString $seoDescription = null,
        public readonly ?Content $content = null,
        public readonly ?SiteLink $siteLink = null,
        public readonly ?RichSnippet $richSnippet = null,
        public readonly array $images = [],
        array $categoriesIds = [],
        array $parametersIds = [],
        public readonly ?string $brandId = self::BRAND_ID_UNSET,
        public readonly ?string $guid = null,
        public readonly ?bool $isLoaded = null,
        public readonly array $categories = [],
        public readonly ?\DateTimeInterface $createdAt = null,
        public readonly ?\DateTimeInterface $updatedAt = null,
        public readonly ?array $variants = null,
    ) {
        $this->categoriesIds = array_values(array_map(
            static fn(mixed $value): string => (string) $value,
            array_filter(
                $categoriesIds,
                static fn(mixed $value): bool => $value !== '' && $value !== null,
            ),
        ));
        $this->parametersIds = array_values(array_map(
            static fn(mixed $value): int => (int) $value,
            array_filter(
                $parametersIds,
                static fn(mixed $value): bool => $value !== '' && $value !== null,
            ),
        ));
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

        if ($this->brandId !== self::BRAND_ID_UNSET) {
            $data['brand_id'] = $this->brandId;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var ProductData $data */
        return new self(
            id: $data['id'],
            isVisible: $data['is_visible'],
            name: LocalizedString::fromArray($data['name']),
            url: LocalizedString::fromArray($data['url']),
            shortDescription: isset($data['short_description']) ? LocalizedString::fromArray($data['short_description']) : null,
            description: isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            seoTitle: isset($data['seo_title']) ? LocalizedString::fromArray($data['seo_title']) : null,
            seoDescription: isset($data['seo_description']) ? LocalizedString::fromArray($data['seo_description']) : null,
            content: isset($data['content']) ? Content::fromArray($data['content']) : null,
            siteLink: isset($data['site_link']) ? SiteLink::fromArray($data['site_link']) : null,
            richSnippet: isset($data['rich_snippet']) ? RichSnippet::fromArray($data['rich_snippet']) : null,
            images: $data['images'] ?? [],
            categoriesIds: $data['categories_ids'] ?? [],
            parametersIds: $data['parameters_ids'] ?? [],
            brandId: array_key_exists('brand_id', $data) ? $data['brand_id'] : self::BRAND_ID_UNSET,
            guid: $data['guid'] ?? null,
            isLoaded: $data['is_loaded'] ?? null,
            categories: $data['categories'] ?? [],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            variants: isset($data['variant'])
                ? array_map(
                    static fn(array $variant): ProductVariant => ProductVariant::fromArray($variant),
                    $data['variant'],
                )
                : null,
        );
    }
}

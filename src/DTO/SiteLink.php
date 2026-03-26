<?php

declare(strict_types=1);

namespace Pobo\Sdk\DTO;

use Pobo\Sdk\Enum\Language;

final class SiteLink
{
    /**
     * @param array<string, string> $html
     * @param array<string, array<SiteLinkItem>> $list
     */
    public function __construct(
        public readonly array $html = [],
        public readonly array $list = [],
    ) {
    }

    public function getHtml(Language $language): ?string
    {
        return $this->html[$language->value] ?? null;
    }

    /**
     * @return array<SiteLinkItem>|null
     */
    public function getList(Language $language): ?array
    {
        return $this->list[$language->value] ?? null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, string> $html */
        $html = $data['html'] ?? [];

        /** @var array<string, array<array{heading: string, slug: string}>> $rawList */
        $rawList = $data['list'] ?? [];

        $list = [];
        foreach ($rawList as $lang => $items) {
            $list[$lang] = array_map(
                fn(array $item) => SiteLinkItem::fromArray($item),
                $items,
            );
        }

        return new self(
            html: $html,
            list: $list,
        );
    }
}

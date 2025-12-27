<?php

declare(strict_types=1);

namespace Pobo\Sdk\Enum;

enum WebhookEvent: string
{
    case PRODUCTS_UPDATE = 'Products.update';
    case CATEGORIES_UPDATE = 'Categories.update';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $value): ?self
    {
        return match ($value) {
            'Products.update' => self::PRODUCTS_UPDATE,
            'Categories.update' => self::CATEGORIES_UPDATE,
            default => null,
        };
    }
}

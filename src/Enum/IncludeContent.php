<?php

declare(strict_types=1);

namespace Pobo\Sdk\Enum;

enum IncludeContent: string
{
    case MARKETPLACE = 'marketplace';
    case NESTED = 'nested';
    case SITE_LINK = 'site_link';
    case RICH_SNIPPET = 'rich_snippet';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}

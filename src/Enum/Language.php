<?php

declare(strict_types=1);

namespace Pobo\Sdk\Enum;

enum Language: string
{
    case DEFAULT = 'default';
    case CS = 'cs';
    case SK = 'sk';
    case EN = 'en';
    case DE = 'de';
    case PL = 'pl';
    case HU = 'hu';

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

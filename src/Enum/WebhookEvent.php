<?php

declare(strict_types=1);

namespace Pobo\Sdk\Enum;

enum WebhookEvent: string
{
    case PRODUCTS_CREATE = 'Products.create';
    case PRODUCTS_UPDATE = 'Products.update';
    case PRODUCTS_DELETE = 'Products.delete';

    case CATEGORIES_CREATE = 'Categories.create';
    case CATEGORIES_UPDATE = 'Categories.update';
    case CATEGORIES_DELETE = 'Categories.delete';

    case BRANDS_CREATE = 'Brands.create';
    case BRANDS_UPDATE = 'Brands.update';
    case BRANDS_DELETE = 'Brands.delete';

    case BLOGS_CREATE = 'Blogs.create';
    case BLOGS_UPDATE = 'Blogs.update';
    case BLOGS_DELETE = 'Blogs.delete';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public function isCreate(): bool
    {
        return str_ends_with($this->value, '.create');
    }

    public function isUpdate(): bool
    {
        return str_ends_with($this->value, '.update');
    }

    public function isDelete(): bool
    {
        return str_ends_with($this->value, '.delete');
    }
}

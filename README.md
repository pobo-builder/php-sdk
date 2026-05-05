# Pobo PHP SDK

[![Tests](https://github.com/pobo-builder/php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/pobo-builder/php-sdk/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/pobo-builder/php-sdk/v/stable)](https://packagist.org/packages/pobo-builder/php-sdk)
[![License](https://poser.pugx.org/pobo-builder/php-sdk/license)](https://packagist.org/packages/pobo-builder/php-sdk)

Official PHP SDK for [Pobo API V2](https://api.pobo.space) - product content management and webhooks.

## Requirements

- PHP 8.1+
- ext-curl
- ext-json

## Installation

```bash
composer require pobo-builder/php-sdk
```

## Quick Start

### Authentication

The SDK uses bearer token authentication. You can obtain your **API token** and **webhook secret** in your Pobo administration under the e-shop settings (REST API section).

The token is sent as `Authorization: Bearer {token}` on every request and is bound to a single e-shop.

### API Client

```php
use Pobo\Sdk\PoboClient;

$client = new PoboClient(
    apiToken: 'your-api-token',
    baseUrl: 'https://api.pobo.space', // optional
    timeout: 30 // optional, in seconds
);
```

## Import

### Import Order

```
1. Parameters (no dependencies)
2. Categories (no dependencies)
3. Brands (no dependencies)
4. Products (depends on categories, parameters, and brands)
5. Blogs (no dependencies)
```

### Multilang Validation Rules

The API enforces a "language consistency" rule across multilang fields:

- `name.default` and `url.default` are **always required**.
- If any language key (e.g. `sk`) appears in any multilang field of an item, then `name.{lang}` and `url.{lang}` become required for that item.
- Other multilang fields (`short_description`, `description`, `seo_title`, `seo_description`) may have `null` values, but the language key must be present in the field if used elsewhere.
- All `url.*` values must start with `https://`.
- Records that fail validation are **skipped**, not aborted — the response reports them in `errors[]` with `index`, `id`, and a list of messages. The rest of the batch is imported.

> **Tip:** The `LocalizedString::withTranslation()` helper sets the value for a given language; use it consistently across `name`, `url` and other fields when adding a new locale to an item.

### Import Parameters

```php
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\ParameterValue;

$parameters = [
    new Parameter(
        id: 1,
        name: 'Color',
        values: [
            new ParameterValue(id: 1, value: 'Red'),
            new ParameterValue(id: 2, value: 'Blue'),
        ],
    ),
    new Parameter(
        id: 2,
        name: 'Size',
        values: [
            new ParameterValue(id: 3, value: 'S'),
            new ParameterValue(id: 4, value: 'M'),
        ],
    ),
];

$result = $client->importParameters($parameters);
echo sprintf('Imported: %d, Values: %d', $result->imported, $result->valuesImported);
```

### Import Categories

```php
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

$categories = [
    new Category(
        id: 'CAT-001',
        isVisible: true,
        name: LocalizedString::create('Electronics')
            ->withTranslation(Language::CS, 'Elektronika')
            ->withTranslation(Language::SK, 'Elektronika'),
        url: LocalizedString::create('https://example.com/electronics')
            ->withTranslation(Language::CS, 'https://example.com/cs/elektronika')
            ->withTranslation(Language::SK, 'https://example.com/sk/elektronika'),
        description: LocalizedString::create('<p>All electronics</p>')
            ->withTranslation(Language::CS, '<p>Veškerá elektronika</p>')
            ->withTranslation(Language::SK, '<p>Všetka elektronika</p>'),
        images: ['https://example.com/images/electronics.jpg'],
    ),
    new Category(
        id: 'CAT-002',
        isVisible: true,
        name: LocalizedString::create('Phones')
            ->withTranslation(Language::CS, 'Telefony')
            ->withTranslation(Language::SK, 'Telefóny'),
        url: LocalizedString::create('https://example.com/phones')
            ->withTranslation(Language::CS, 'https://example.com/cs/telefony')
            ->withTranslation(Language::SK, 'https://example.com/sk/telefony'),
    ),
];

$result = $client->importCategories($categories);
echo sprintf('Imported: %d, Updated: %d', $result->imported, $result->updated);
```

### Import Brands

```php
use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

$brands = [
    new Brand(
        id: 'BRAND-001',
        isVisible: true,
        name: LocalizedString::create('Apple')
            ->withTranslation(Language::CS, 'Apple')
            ->withTranslation(Language::SK, 'Apple'),
        url: LocalizedString::create('https://example.com/brands/apple')
            ->withTranslation(Language::CS, 'https://example.com/cs/znacky/apple')
            ->withTranslation(Language::SK, 'https://example.com/sk/znacky/apple'),
        imagePreview: 'https://example.com/brands/apple-logo.png',
        description: LocalizedString::create('<p>Apple Inc.</p>')
            ->withTranslation(Language::CS, '<p>Apple Inc.</p>'),
    ),
];

$result = $client->importBrands($brands);
echo sprintf('Imported: %d, Updated: %d', $result->imported, $result->updated);
```

> **Brand → Product pairing:** brands stand on their own. To pair a product with a brand, set the `brandId` argument on the `Product` DTO (see "Import Products" below). Brands must be imported **before** the products that reference them.

### Import Products

```php
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

$products = [
    new Product(
        id: 'PROD-001',
        isVisible: true,
        name: LocalizedString::create('iPhone 15')
            ->withTranslation(Language::CS, 'iPhone 15')
            ->withTranslation(Language::SK, 'iPhone 15'),
        url: LocalizedString::create('https://example.com/iphone-15')
            ->withTranslation(Language::CS, 'https://example.com/cs/iphone-15')
            ->withTranslation(Language::SK, 'https://example.com/sk/iphone-15'),
        shortDescription: LocalizedString::create('Latest iPhone model')
            ->withTranslation(Language::CS, 'Nejnovější model iPhone')
            ->withTranslation(Language::SK, 'Najnovší model iPhone'),
        description: LocalizedString::create('<p>The best iPhone ever.</p>')
            ->withTranslation(Language::CS, '<p>Nejlepší iPhone vůbec.</p>')
            ->withTranslation(Language::SK, '<p>Najlepší iPhone vôbec.</p>'),
        images: ['https://example.com/images/iphone-1.jpg'],
        categoriesIds: ['CAT-001', 'CAT-002'],
        parametersIds: [1, 2],
    ),
    new Product(
        id: 'PROD-002',
        isVisible: true,
        name: LocalizedString::create('Samsung Galaxy S24')
            ->withTranslation(Language::CS, 'Samsung Galaxy S24')
            ->withTranslation(Language::SK, 'Samsung Galaxy S24'),
        url: LocalizedString::create('https://example.com/samsung-s24')
            ->withTranslation(Language::CS, 'https://example.com/cs/samsung-s24')
            ->withTranslation(Language::SK, 'https://example.com/sk/samsung-s24'),
        categoriesIds: ['CAT-001'],
        parametersIds: [1, 3],
    ),
];

$result = $client->importProducts($products);

if ($result->hasErrors() === true) {
    foreach ($result->errors as $error) {
        echo sprintf('Error: %s', implode(', ', $error['errors']));
    }
}
```

#### Pairing a product with a brand

The `brandId` argument on `Product` follows three-state semantics matching the API:

| Value of `brandId`             | Behavior                                                                  |
|--------------------------------|---------------------------------------------------------------------------|
| **omitted** (default)          | `brand_id` is **not sent** — the server keeps the existing assignment.    |
| `null`                         | `brand_id: null` is sent — the brand is **cleared** on the product.       |
| `'BRAND-001'` (string)         | `brand_id: "BRAND-001"` is sent — paired with `brand.remote_id` in Pobo.  |

```php
// Set a brand
$product = new Product(
    id: 'PROD-001',
    isVisible: true,
    name: LocalizedString::create('iPhone 15'),
    url: LocalizedString::create('https://example.com/iphone-15'),
    brandId: 'BRAND-001',
);

// Clear an existing brand assignment
$product = new Product(
    id: 'PROD-001',
    isVisible: true,
    name: LocalizedString::create('iPhone 15'),
    url: LocalizedString::create('https://example.com/iphone-15'),
    brandId: null,
);

// Leave the brand untouched (the default — do not pass brandId)
$product = new Product(
    id: 'PROD-001',
    isVisible: true,
    name: LocalizedString::create('iPhone 15'),
    url: LocalizedString::create('https://example.com/iphone-15'),
);
```

> **Note:** the brand referenced by `brandId` must already exist in Pobo (registered via `importBrands`). Otherwise the product is skipped with `"Invalid brand id: ..."`.

### Import Blogs

```php
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

$blogs = [
    new Blog(
        id: 'BLOG-001',
        isVisible: true,
        name: LocalizedString::create('New Product Launch')
            ->withTranslation(Language::CS, 'Uvedení nového produktu')
            ->withTranslation(Language::SK, 'Uvedenie nového produktu'),
        url: LocalizedString::create('https://example.com/blog/new-product')
            ->withTranslation(Language::CS, 'https://example.com/cs/blog/novy-produkt')
            ->withTranslation(Language::SK, 'https://example.com/sk/blog/novy-produkt'),
        category: 'news',
        description: LocalizedString::create('<p>We are excited to announce...</p>')
            ->withTranslation(Language::CS, '<p>S radostí oznamujeme...</p>')
            ->withTranslation(Language::SK, '<p>S radosťou oznamujeme...</p>'),
        images: ['https://example.com/images/blog-1.jpg'],
    ),
    new Blog(
        id: 'BLOG-002',
        isVisible: true,
        name: LocalizedString::create('How to Choose')
            ->withTranslation(Language::CS, 'Jak vybrat')
            ->withTranslation(Language::SK, 'Ako vybrať'),
        url: LocalizedString::create('https://example.com/blog/how-to-choose')
            ->withTranslation(Language::CS, 'https://example.com/cs/blog/jak-vybrat')
            ->withTranslation(Language::SK, 'https://example.com/sk/blog/ako-vybrat'),
        category: 'tips',
    ),
];

$result = $client->importBlogs($blogs);
echo sprintf('Imported: %d, Updated: %d', $result->imported, $result->updated);
```

## Delete

### Delete Products

```php
$result = $client->deleteProducts(['PROD-001', 'PROD-002', 'PROD-003']);

echo sprintf('Deleted: %d, Skipped: %d', $result->deleted, $result->skipped);

if ($result->hasErrors() === true) {
    foreach ($result->errors as $error) {
        echo sprintf('ID %s: %s', $error['id'], implode(', ', $error['errors']));
    }
}
```

### Delete Categories

```php
$result = $client->deleteCategories(['CAT-001', 'CAT-002']);
echo sprintf('Deleted: %d', $result->deleted);
```

### Delete Brands

```php
$result = $client->deleteBrands(['BRAND-001', 'BRAND-002']);
echo sprintf('Deleted: %d', $result->deleted);
```

> **Note:** soft-deleting a brand does **not** clear `brand_id` on the products that reference it. Re-import the products with `brandId: null` if you also want to drop the assignment.

### Delete Blogs

```php
$result = $client->deleteBlogs(['BLOG-001', 'BLOG-002']);
echo sprintf('Deleted: %d', $result->deleted);
```

> **Note:** Delete performs a soft-delete.

## Export

> **Important:** When `isEdited` is not provided, the server applies a default of `true` and returns **only products/categories/blogs that have been edited in Pobo** (`is_loaded = true`). To export everything, pass `isEdited: false` explicitly.

### Export Products

```php
$response = $client->getProducts(page: 1, perPage: 50);

foreach ($response->data as $product) {
    echo sprintf("%s: %s\n", $product->id, $product->name->getDefault());
}

echo sprintf('Page %d of %d', $response->currentPage, $response->getTotalPages());

// Iterate through all products (handles pagination automatically)
foreach ($client->iterateProducts() as $product) {
    echo sprintf("%s: %s\n", $product->id, $product->name->getDefault());
}

// Filter by last update time (sent to API as Y-m-d H:i:s in server timezone)
$since = new DateTime('2024-01-01 00:00:00');
$response = $client->getProducts(lastUpdateFrom: $since);

// Filter only edited products (= is_loaded on server side)
$response = $client->getProducts(isEdited: true);

// Get ALL products including ones never edited in Pobo
$response = $client->getProducts(isEdited: false);

// Include optional content (marketplace HTML, raw widget JSON)
use Pobo\Sdk\Enum\IncludeContent;

$response = $client->getProducts(include: [IncludeContent::MARKETPLACE, IncludeContent::NESTED]);
```

### Export Categories

```php
$response = $client->getCategories();

foreach ($response->data as $category) {
    echo sprintf("%s: %s\n", $category->id, $category->name->getDefault());
}

// Iterate through all categories
foreach ($client->iterateCategories() as $category) {
    processCategory($category);
}
```

### Export Brands

```php
$response = $client->getBrands();

foreach ($response->data as $brand) {
    echo sprintf("%s: %s (logo: %s)\n", $brand->id, $brand->name->getDefault(), $brand->imagePreview ?? 'none');
}

// Iterate through all brands
foreach ($client->iterateBrands() as $brand) {
    processBrand($brand);
}
```

### Export Blogs

```php
$response = $client->getBlogs();

foreach ($response->data as $blog) {
    echo sprintf("%s: %s\n", $blog->id, $blog->name->getDefault());
}

// Iterate through all blogs
foreach ($client->iterateBlogs() as $blog) {
    processBlog($blog);
}
```

## Language Filtering

By default, only the `default` language is returned. Use the `lang` parameter to request specific languages:

```php
use Pobo\Sdk\Enum\Language;

// Get all languages
$response = $client->getProducts(lang: [Language::ALL]);

// Get specific languages
$response = $client->getProducts(lang: [Language::DEFAULT, Language::CS, Language::SK]);

// Iterate with language filter
foreach ($client->iterateProducts(lang: [Language::ALL]) as $product) {
    echo $product->name->get(Language::CS);
    echo $product->content?->getHtml(Language::CS);
}

// Same for categories, brands and blogs
$response = $client->getCategories(lang: [Language::DEFAULT, Language::CS]);
$response = $client->getBrands(lang: [Language::ALL]);
$response = $client->getBlogs(lang: [Language::ALL]);
```

> **Note:** Without the `lang` parameter, only `default` is returned. Invalid language values are silently ignored.

## Content (HTML/Marketplace/Nested)

By default, only `content.html` is returned. Use the `include` parameter to request additional content:

| Value          | Description                                       | Available for                   |
|----------------|---------------------------------------------------|---------------------------------|
| `marketplace`  | HTML content for marketplace (no custom CSS)      | product, category, brand, blog  |
| `nested`       | Raw widget JSON from widget tables                | product, category, brand, blog  |
| `site_link`    | Anchor navigation on H2 headings                  | product, brand, blog            |
| `rich_snippet` | JSON-LD structured data (FAQPage)                 | product, category, brand, blog  |

```php
use Pobo\Sdk\Enum\IncludeContent;
use Pobo\Sdk\Enum\Language;

// Include marketplace and nested content
foreach ($client->iterateProducts(include: [IncludeContent::MARKETPLACE, IncludeContent::NESTED]) as $product) {
    if ($product->content !== null) {
        // Get HTML content for web (always included)
        $htmlCs = $product->content->getHtml(Language::CS);
        $htmlSk = $product->content->getHtml(Language::SK);
        $htmlEn = $product->content->getHtml(Language::EN);

        // Get content for marketplace (requires include: ['marketplace'])
        $marketplaceCs = $product->content->getMarketplace(Language::CS);
        $marketplaceSk = $product->content->getMarketplace(Language::SK);

        // Get raw widget JSON (requires include: ['nested'])
        $nested = $product->content->getNested();

        // Get default content
        $htmlDefault = $product->content->getHtmlDefault();
        $marketplaceDefault = $product->content->getMarketplaceDefault();
    }
}

// Same for categories
foreach ($client->iterateCategories(include: [IncludeContent::NESTED]) as $category) {
    if ($category->content !== null) {
        echo $category->content->getHtml(Language::CS);
        $nested = $category->content->getNested();
    }
}

// Same for brands
foreach ($client->iterateBrands(include: [IncludeContent::MARKETPLACE]) as $brand) {
    if ($brand->content !== null) {
        echo $brand->content->getHtml(Language::CS);
        echo $brand->content->getMarketplace(Language::CS);
    }
}

// Same for blogs
foreach ($client->iterateBlogs(include: [IncludeContent::MARKETPLACE]) as $blog) {
    if ($blog->content !== null) {
        echo $blog->content->getHtml(Language::CS);
        echo $blog->content->getMarketplace(Language::CS);
    }
}
```

## Site Links

Anchor navigation generated from H2 headings in content widgets. Available for products, brands, and blogs.

> **E-shop config required:** Site links are returned only if `enable_site_link` is enabled on the e-shop in Pobo administration. Without that flag the `site_link` field will be empty even when `IncludeContent::SITE_LINK` is requested.

```php
use Pobo\Sdk\Enum\IncludeContent;
use Pobo\Sdk\Enum\Language;

foreach ($client->iterateProducts(include: [IncludeContent::SITE_LINK], lang: [Language::ALL]) as $product) {
    if ($product->siteLink !== null) {
        // Get rendered navigation HTML
        $navHtml = $product->siteLink->getHtml(Language::DEFAULT);

        // Get structured list of headings
        $items = $product->siteLink->getList(Language::DEFAULT);
        foreach ($items as $item) {
            echo sprintf('<a href="#%s">%s</a>', $item->slug, $item->heading);
        }
    }
}
```

## Rich Snippets

JSON-LD structured data (FAQPage schema) generated from FAQ widgets. Available for products, categories, brands, and blogs.

> **E-shop config required:** Rich snippets are returned only if `enable_rich_snippet` is enabled on the e-shop in Pobo administration. Without that flag the `rich_snippet` field will be empty even when `IncludeContent::RICH_SNIPPET` is requested.

```php
use Pobo\Sdk\Enum\IncludeContent;
use Pobo\Sdk\Enum\Language;

foreach ($client->iterateProducts(include: [IncludeContent::RICH_SNIPPET], lang: [Language::ALL]) as $product) {
    if ($product->richSnippet !== null) {
        // Get rendered JSON-LD script tag
        $scriptHtml = $product->richSnippet->getHtml(Language::DEFAULT);

        // Get parsed JSON-LD object
        $jsonLd = $product->richSnippet->getJson(Language::DEFAULT);
        echo $jsonLd['@type']; // 'FAQPage'
    }
}

// Categories have rich snippets but no site links
foreach ($client->iterateCategories(include: [IncludeContent::RICH_SNIPPET]) as $category) {
    if ($category->richSnippet !== null) {
        echo $category->richSnippet->getHtml(Language::DEFAULT);
    }
}

// Brands support both site_link and rich_snippet
foreach ($client->iterateBrands(include: [IncludeContent::RICH_SNIPPET, IncludeContent::SITE_LINK]) as $brand) {
    if ($brand->richSnippet !== null) {
        echo $brand->richSnippet->getHtml(Language::DEFAULT);
    }
    if ($brand->siteLink !== null) {
        echo $brand->siteLink->getHtml(Language::DEFAULT);
    }
}
```

## Webhook Handler

Pobo can notify your application when content is changed in the administration. You register a webhook URL in the Pobo e-shop settings and receive `POST` requests signed with HMAC-SHA256.

### Events

| Event                | Constant                          | Fired when                                         |
|----------------------|-----------------------------------|----------------------------------------------------|
| `Products.update`    | `WebhookEvent::PRODUCTS_UPDATE`   | Any product content was edited and saved in Pobo   |
| `Categories.update`  | `WebhookEvent::CATEGORIES_UPDATE` | Any category content was edited and saved in Pobo  |
| `Blogs.update`       | `WebhookEvent::BLOGS_UPDATE`      | Any blog content was edited and saved in Pobo      |

Each request carries:

- Header `X-Webhook-Signature` — HMAC-SHA256 of the raw body using your webhook secret.
- Header `X-Webhook-Event` — event name (informational; the SDK reads the event from the body).
- JSON body — `{ "event": "...", "timestamp": "ISO-8601", "eshop_id": 123 }`.

The webhook does **not** carry the changed entities themselves; it is a notification to trigger a sync via the export endpoints (`getProducts` / `iterateProducts` etc., typically combined with `lastUpdateFrom`).

### Basic Usage

```php
use Pobo\Sdk\WebhookHandler;
use Pobo\Sdk\Enum\WebhookEvent;
use Pobo\Sdk\Exception\WebhookException;

$handler = new WebhookHandler(webhookSecret: 'your-webhook-secret');

try {
    $payload = $handler->handleFromGlobals();

    match ($payload->event) {
        WebhookEvent::PRODUCTS_UPDATE => syncProducts($client),
        WebhookEvent::CATEGORIES_UPDATE => syncCategories($client),
        WebhookEvent::BLOGS_UPDATE => syncBlogs($client),
    };

    http_response_code(200);
    echo json_encode(['status' => 'ok']);

} catch (WebhookException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Manual Handling

```php
$payload = $handler->handle(
    payload: file_get_contents('php://input'),
    signature: $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? ''
);
```

### Webhook Payload

```php
$payload->event;     // WebhookEvent enum
$payload->timestamp; // DateTimeInterface
$payload->eshopId;   // int
```

## Error Handling

```php
use Pobo\Sdk\Exception\ApiException;
use Pobo\Sdk\Exception\ValidationException;
use Pobo\Sdk\Exception\WebhookException;

try {
    $result = $client->importProducts($products);
} catch (ValidationException $e) {
    echo sprintf('Validation error: %s', $e->getMessage());
    print_r($e->errors);
} catch (ApiException $e) {
    echo sprintf('API error (%d): %s', $e->httpCode, $e->getMessage());
    print_r($e->responseBody);
}
```

### Exception Types

| Exception              | Thrown when                                                                                                               |
|------------------------|---------------------------------------------------------------------------------------------------------------------------|
| `ValidationException`  | Local pre-flight checks fail — empty payload or **more than 100 items** in a bulk import/delete. The HTTP request is not sent. |
| `ApiException`         | The HTTP request fails (network/cURL error) or the API returns `>= 400`. `httpCode` and parsed `responseBody` are exposed. |
| `WebhookException`     | The webhook signature is missing/invalid, the body cannot be parsed, or the event name is unknown.                        |

> **Note:** Per-item validation failures during bulk import (e.g. invalid URL, missing language) do **not** raise `ApiException`. They appear in `$result->errors[]` while the rest of the batch is processed. Always inspect `$result->hasErrors()` after a successful call.

## Localized Strings

```php
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

// Create with default value
$name = LocalizedString::create('Default Name');

// Add translations using fluent interface
$name = $name
    ->withTranslation(Language::CS, 'Czech Name')
    ->withTranslation(Language::SK, 'Slovak Name')
    ->withTranslation(Language::EN, 'English Name');

// Get values
$name->getDefault();         // 'Default Name'
$name->get(Language::CS);    // 'Czech Name'
$name->toArray();            // ['default' => '...', 'cs' => '...', ...]
```

> **About `default`:** `default` is a separate, virtual locale — **not an alias for `cs` or any other language**. It is the fallback content used by Pobo when a specific language variant is missing. `name.default` and `url.default` are required on import; per-language values (`cs`, `sk`, …) are optional and follow the multilang validation rule above.

### Supported Languages

| Code      | Language           |
|-----------|--------------------|
| `default` | Default (required) |
| `cs`      | Czech              |
| `sk`      | Slovak             |
| `en`      | English            |
| `de`      | German             |
| `pl`      | Polish             |
| `hu`      | Hungarian          |

## API Methods

| Method                                                                                                                | Description                      |
|-----------------------------------------------------------------------------------------------------------------------|----------------------------------|
| `importProducts(array $products)`                                                                                     | Bulk import products (max 100)   |
| `importCategories(array $categories)`                                                                                 | Bulk import categories (max 100) |
| `importBrands(array $brands)`                                                                                         | Bulk import brands (max 100)     |
| `importParameters(array $parameters)`                                                                                 | Bulk import parameters (max 100) |
| `importBlogs(array $blogs)`                                                                                           | Bulk import blogs (max 100)      |
| `deleteProducts(array $ids)`                                                                                          | Bulk delete products (max 100)   |
| `deleteCategories(array $ids)`                                                                                        | Bulk delete categories (max 100) |
| `deleteBrands(array $ids)`                                                                                            | Bulk delete brands (max 100)     |
| `deleteBlogs(array $ids)`                                                                                             | Bulk delete blogs (max 100)      |
| `getProducts(?int $page, ?int $perPage, ?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`   | Get products page                |
| `getCategories(?int $page, ?int $perPage, ?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)` | Get categories page              |
| `getBrands(?int $page, ?int $perPage, ?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`     | Get brands page                  |
| `getBlogs(?int $page, ?int $perPage, ?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`      | Get blogs page                   |
| `iterateProducts(?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`                          | Iterate all products             |
| `iterateCategories(?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`                        | Iterate all categories           |
| `iterateBrands(?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`                            | Iterate all brands               |
| `iterateBlogs(?DateTime $lastUpdateFrom, ?bool $isEdited, ?array $include, ?array $lang)`                             | Iterate all blogs                |

## Limits

| Limit                        | Value        |
|------------------------------|--------------|
| Max items per import request | 100          |
| Max items per delete request | 100          |
| Max items per export page    | 100          |
| Product/Category ID length   | 255 chars    |
| Name length                  | 250 chars    |
| URL length                   | 255 chars    |
| Image URL length             | 650 chars    |
| Description length           | 500,000 chars |
| SEO description length       | 500 chars    |

## Testing

### Unit Tests

```bash
vendor/bin/phpunit --testsuite=Unit
```

Unit tests are pure (no network) and run on every CI build across PHP 8.1–8.5.

### Integration Tests

Integration tests hit the real `api.pobo.space` API and verify that the SDK's wire format is in sync with the server. They require a dedicated test e-shop and a valid REST API token.

```bash
POBO_API_TOKEN="your-test-eshop-token" vendor/bin/phpunit --testsuite=Integration
```

If `POBO_API_TOKEN` is not set, all tests in the suite are marked **skipped** (not failed).

The CI workflow runs integration tests automatically on:

- pushes to `master`,
- PRs **from the same repository** (PRs from forks are skipped — secrets are not exposed there),
- manual dispatch (`workflow_dispatch`).

Each run uses a unique ID prefix combining `GITHUB_RUN_ID` and a random suffix, and `tearDown()` removes anything the test created. Two PRs running simultaneously cannot collide.

## License

MIT License

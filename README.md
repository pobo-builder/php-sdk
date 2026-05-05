# Pobo PHP SDK

[![Tests](https://github.com/pobo-builder/php-sdk/actions/workflows/tests.yml/badge.svg)](https://github.com/pobo-builder/php-sdk/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/pobo-builder/php-sdk/v/stable)](https://packagist.org/packages/pobo-builder/php-sdk)
[![License](https://poser.pugx.org/pobo-builder/php-sdk/license)](https://packagist.org/packages/pobo-builder/php-sdk)

Official PHP SDK for [Pobo API V2](https://api.pobo.space) — product content management and webhooks.

## Requirements

- PHP 8.1+
- ext-curl, ext-json

## Installation

```bash
composer require pobo-builder/php-sdk
```

## Quick Start

```php
use Pobo\Sdk\PoboClient;

$client = new PoboClient(apiToken: 'your-api-token');
```

The token authenticates via `Authorization: Bearer ...` and is bound to a single e-shop. Get the API token and webhook secret in the Pobo administration under e-shop settings → REST API.

The examples below use only the **default** language. For multi-language imports and exports see [Multilang](#multilang) further down.

## Import

Order: parameters → categories → brands → products → blogs.

### Parameters

```php
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\ParameterValue;

$result = $client->importParameters([
    new Parameter(id: 1, name: 'Color', values: [
        new ParameterValue(id: 1, value: 'Red'),
        new ParameterValue(id: 2, value: 'Blue'),
    ]),
]);
```

### Categories

```php
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;

$result = $client->importCategories([
    new Category(
        id: 'CAT-001',
        isVisible: true,
        name: LocalizedString::create('Electronics'),
        url: LocalizedString::create('https://example.com/electronics'),
    ),
]);
```

### Brands

```php
use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\LocalizedString;

$result = $client->importBrands([
    new Brand(
        id: 'BRAND-001',
        isVisible: true,
        name: LocalizedString::create('Apple'),
        url: LocalizedString::create('https://example.com/znacky/apple'),
        imagePreview: 'https://example.com/brands/apple-logo.png',
    ),
]);
```

### Products

```php
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\LocalizedString;

$result = $client->importProducts([
    new Product(
        id: 'PROD-001',
        isVisible: true,
        name: LocalizedString::create('iPhone 15'),
        url: LocalizedString::create('https://example.com/iphone-15'),
        categoriesIds: ['CAT-001'],
        parametersIds: [1, 2],
        brandId: 'BRAND-001',
    ),
]);

if ($result->hasErrors()) {
    foreach ($result->errors as $error) {
        echo implode(', ', $error['errors']);
    }
}
```

#### Pairing a product with a brand

`brandId` has three-state semantics:

| Value of `brandId`     | Behavior                                                          |
|------------------------|-------------------------------------------------------------------|
| omitted (default)      | `brand_id` is not sent — the server keeps the existing assignment |
| `null`                 | brand is **cleared** on the product                               |
| `'BRAND-001'` (string) | paired with `brand.remote_id` in Pobo                             |

The brand referenced by `brandId` must already exist in Pobo (registered via `importBrands`); otherwise the product is skipped with `"Invalid brand id: ..."`.

### Blogs

```php
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\LocalizedString;

$result = $client->importBlogs([
    new Blog(
        id: 'BLOG-001',
        isVisible: true,
        name: LocalizedString::create('New Product Launch'),
        url: LocalizedString::create('https://example.com/blog/new-product'),
        category: 'news',
    ),
]);
```

> **Per-item errors:** validation failures during bulk import (invalid URL, missing language, …) **don't** raise an exception. They appear in `$result->errors[]` while the rest of the batch is processed. Always inspect `$result->hasErrors()`.

## Delete

Soft-delete by external ID (max 100 per request):

```php
$client->deleteProducts(['PROD-001', 'PROD-002']);
$client->deleteCategories(['CAT-001']);
$client->deleteBrands(['BRAND-001']);
$client->deleteBlogs(['BLOG-001']);
```

> Soft-deleting a brand does **not** clear `brand_id` on products that reference it. Re-import the products with `brandId: null` if you also want to drop the assignment.

## Export

```php
$response = $client->getProducts(page: 1, perPage: 50);

foreach ($response->data as $product) {
    echo $product->id, ': ', $product->name->getDefault(), "\n";
}

// Or iterate everything (handles pagination automatically):
foreach ($client->iterateProducts() as $product) {
    // ...
}
```

The same shape works for `getCategories` / `getBrands` / `getBlogs` (and their `iterate*` variants).

> **`isEdited`:** when not provided, the server defaults to `true` and returns only items that have been edited in Pobo (`is_loaded = true`). To export everything, pass `isEdited: false` explicitly.

### Filter by last update

```php
$since = new DateTime('2024-01-01 00:00:00');
$response = $client->getProducts(lastUpdateFrom: $since);
```

### Generated content (HTML, marketplace, …)

By default the export returns only `content.html`. Use `include` to request additional content:

```php
use Pobo\Sdk\Enum\IncludeContent;

foreach ($client->iterateProducts(include: [IncludeContent::MARKETPLACE]) as $product) {
    $html            = $product->content?->getHtmlDefault();
    $marketplaceHtml = $product->content?->getMarketplaceDefault();
}
```

| Value          | Description                                  | Available for                  |
|----------------|----------------------------------------------|--------------------------------|
| `marketplace`  | HTML for marketplace (no custom CSS)         | product, category, brand, blog |
| `nested`       | Raw widget JSON                              | product, category, brand, blog |
| `site_link`    | Anchor navigation on H2 headings             | product, brand, blog           |
| `rich_snippet` | JSON-LD structured data (FAQPage)            | product, category, brand, blog |

`site_link` requires `enable_site_link` and `rich_snippet` requires `enable_rich_snippet` to be enabled on the e-shop in Pobo administration.

## Multilang

Pobo supports 7 languages: `default`, `cs`, `sk`, `en`, `de`, `pl`, `hu`. `default` is a separate virtual locale used as fallback content — it is **not** an alias for `cs` or any other language.

| Code      | Language           |
|-----------|--------------------|
| `default` | Default (required) |
| `cs`      | Czech              |
| `sk`      | Slovak             |
| `en`      | English            |
| `de`      | German             |
| `pl`      | Polish             |
| `hu`      | Hungarian          |

### Importing translations

Use `LocalizedString::withTranslation()` to chain languages on any localized field:

```php
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

$name = LocalizedString::create('iPhone 15')                    // default
    ->withTranslation(Language::CS, 'iPhone 15 CZ')
    ->withTranslation(Language::SK, 'iPhone 15 SK')
    ->withTranslation(Language::EN, 'iPhone 15');

$name->getDefault();        // 'iPhone 15'
$name->get(Language::CS);   // 'iPhone 15 CZ'
$name->toArray();           // ['default' => '...', 'cs' => '...', 'sk' => '...', 'en' => '...']
```

Pass localized strings to any DTO that accepts them — products, categories, brands, blogs:

```php
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

new Product(
    id: 'PROD-001',
    isVisible: true,
    name: LocalizedString::create('iPhone 15')
        ->withTranslation(Language::CS, 'iPhone 15 CZ')
        ->withTranslation(Language::SK, 'iPhone 15 SK'),
    url: LocalizedString::create('https://example.com/iphone-15')
        ->withTranslation(Language::CS, 'https://example.com/cs/iphone-15')
        ->withTranslation(Language::SK, 'https://example.com/sk/iphone-15'),
    shortDescription: LocalizedString::create('Latest iPhone model')
        ->withTranslation(Language::CS, 'Nejnovější model iPhone')
        ->withTranslation(Language::SK, 'Najnovší model iPhone'),
    description: LocalizedString::create('<p>The best iPhone ever.</p>')
        ->withTranslation(Language::CS, '<p>Nejlepší iPhone vůbec.</p>')
        ->withTranslation(Language::SK, '<p>Najlepší iPhone vôbec.</p>'),
);
```

### Validation rules

The API enforces a "language consistency" rule across multilang fields:

- `name.default` and `url.default` are **always required**.
- If any field on an item carries a translation key (e.g. `sk`), then `name.{lang}` and `url.{lang}` become required for that item.
- Other fields (`short_description`, `description`, `seo_title`, `seo_description`) may have `null` values, but the language key must be present if the language is used elsewhere on the item.
- All `url.*` values must start with `https://`.
- Records that fail validation are **skipped**, not aborted — the response reports them in `errors[]`. The rest of the batch is imported.
- These rules apply identically to products, categories, brands and blogs.

### Update behavior

When you re-import an item:

- Languages **present** in the request stay active (`*_meta.is_active = true`).
- Languages **missing** from the request are soft-deleted (`*_meta.is_active = false`).

Always re-send every language you want to keep visible.

### Filtering languages on export

By default, only `default` is returned. Use the `lang` parameter to request specific languages:

```php
use Pobo\Sdk\Enum\Language;

// All 7 languages
$response = $client->getProducts(lang: [Language::ALL]);

// Specific languages
$response = $client->getProducts(lang: [Language::DEFAULT, Language::CS, Language::SK]);

foreach ($client->iterateProducts(lang: [Language::ALL]) as $product) {
    echo $product->name->get(Language::CS);
    echo $product->content?->getHtml(Language::CS);
}
```

The `lang` parameter filters every multilang field in the response — `name`, `description`, `url`, …, plus `content.html`, `content.marketplace`, `site_link.*`, `rich_snippet.*`. Invalid language values are silently ignored.

### Reading translations

```php
use Pobo\Sdk\Enum\Language;

foreach ($client->iterateProducts(lang: [Language::ALL]) as $product) {
    $product->name->getDefault();           // default value
    $product->name->get(Language::CS);      // Czech variant (or null)
    $product->name->toArray();              // ['default' => '...', 'cs' => '...', ...]

    // Same accessors on content / site_link / rich_snippet:
    $product->content?->getHtml(Language::CS);
    $product->content?->getMarketplace(Language::CS);
    $product->siteLink?->getList(Language::CS);
    $product->richSnippet?->getJson(Language::CS);
}
```

## Webhook Handler

Pobo can notify your application when content changes in the administration. Register a webhook URL in the Pobo e-shop settings; Pobo POSTs to it with HMAC-SHA256 signed requests.

| Event                | Constant                          | Fired when                             |
|----------------------|-----------------------------------|----------------------------------------|
| `Products.update`    | `WebhookEvent::PRODUCTS_UPDATE`   | a product was edited and saved in Pobo |
| `Categories.update`  | `WebhookEvent::CATEGORIES_UPDATE` | a category was edited                  |
| `Blogs.update`       | `WebhookEvent::BLOGS_UPDATE`      | a blog was edited                      |

The webhook is a notification only — it does not carry the changed entities. Use it as a trigger to re-sync via the export endpoints (typically combined with `lastUpdateFrom`).

```php
use Pobo\Sdk\WebhookHandler;
use Pobo\Sdk\Enum\WebhookEvent;
use Pobo\Sdk\Exception\WebhookException;

$handler = new WebhookHandler(webhookSecret: 'your-webhook-secret');

try {
    $payload = $handler->handleFromGlobals();

    match ($payload->event) {
        WebhookEvent::PRODUCTS_UPDATE   => syncProducts($client),
        WebhookEvent::CATEGORIES_UPDATE => syncCategories($client),
        WebhookEvent::BLOGS_UPDATE      => syncBlogs($client),
    };

    http_response_code(200);
} catch (WebhookException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
```

`$payload` exposes `event` (`WebhookEvent` enum), `timestamp` (`DateTimeInterface`), and `eshopId` (int).

## Error Handling

```php
use Pobo\Sdk\Exception\ApiException;
use Pobo\Sdk\Exception\ValidationException;

try {
    $result = $client->importProducts($products);
} catch (ValidationException $e) {
    print_r($e->errors);
} catch (ApiException $e) {
    echo $e->httpCode, ': ', $e->getMessage();
    print_r($e->responseBody);
}
```

| Exception              | Thrown when                                                                                                              |
|------------------------|--------------------------------------------------------------------------------------------------------------------------|
| `ValidationException`  | Local pre-flight check fails — empty payload or **more than 100 items** in a bulk request. The HTTP request is not sent. |
| `ApiException`         | The HTTP request fails or the API returns `>= 400`. Exposes `httpCode` and parsed `responseBody`.                        |
| `WebhookException`     | The webhook signature is missing/invalid, the body cannot be parsed, or the event name is unknown.                       |

> Per-item validation failures during bulk import (invalid URL, missing language, …) **don't** raise `ApiException` — they appear in `$result->errors[]` while the rest of the batch is processed.

## API Methods

| Method                                                                          | Description                      |
|---------------------------------------------------------------------------------|----------------------------------|
| `importProducts(array $products)`                                               | Bulk import products (max 100)   |
| `importCategories(array $categories)`                                           | Bulk import categories (max 100) |
| `importBrands(array $brands)`                                                   | Bulk import brands (max 100)     |
| `importParameters(array $parameters)`                                           | Bulk import parameters (max 100) |
| `importBlogs(array $blogs)`                                                     | Bulk import blogs (max 100)      |
| `deleteProducts(array $ids)`                                                    | Bulk delete products (max 100)   |
| `deleteCategories(array $ids)`                                                  | Bulk delete categories (max 100) |
| `deleteBrands(array $ids)`                                                      | Bulk delete brands (max 100)     |
| `deleteBlogs(array $ids)`                                                       | Bulk delete blogs (max 100)      |
| `getProducts(?page, ?perPage, ?lastUpdateFrom, ?isEdited, ?include, ?lang)`     | Get products page                |
| `getCategories(?page, ?perPage, ?lastUpdateFrom, ?isEdited, ?include, ?lang)`   | Get categories page              |
| `getBrands(?page, ?perPage, ?lastUpdateFrom, ?isEdited, ?include, ?lang)`       | Get brands page                  |
| `getBlogs(?page, ?perPage, ?lastUpdateFrom, ?isEdited, ?include, ?lang)`        | Get blogs page                   |
| `iterateProducts(?lastUpdateFrom, ?isEdited, ?include, ?lang)`                  | Iterate all products             |
| `iterateCategories(?lastUpdateFrom, ?isEdited, ?include, ?lang)`                | Iterate all categories           |
| `iterateBrands(?lastUpdateFrom, ?isEdited, ?include, ?lang)`                    | Iterate all brands               |
| `iterateBlogs(?lastUpdateFrom, ?isEdited, ?include, ?lang)`                     | Iterate all blogs                |

## Limits

| Limit                        | Value         |
|------------------------------|---------------|
| Max items per import request | 100           |
| Max items per delete request | 100           |
| Max items per export page    | 100           |
| Product/Category ID length   | 255 chars     |
| Name length                  | 250 chars     |
| URL length                   | 255 chars     |
| Image URL length             | 650 chars     |
| Description length           | 500,000 chars |
| SEO description length       | 500 chars     |

## Testing

### Unit tests

```bash
vendor/bin/phpunit --testsuite=Unit
```

Unit tests are pure (no network) and run on every CI build across PHP 8.1–8.5.

### Integration tests

Integration tests hit the real `api.pobo.space` API and verify that the SDK's wire format is in sync with the server. They require a dedicated test e-shop and a valid REST API token.

```bash
POBO_API_TOKEN="your-test-eshop-token" vendor/bin/phpunit --testsuite=Integration
```

If `POBO_API_TOKEN` is not set, all integration tests are marked **skipped** (not failed). The CI workflow runs integration tests on pushes to `master`, PRs from the same repository (forks are skipped — secrets are not exposed there), and manual dispatch. Each run uses a unique ID prefix and `tearDown()` removes anything the test created.

## License

MIT License

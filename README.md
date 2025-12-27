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

### API Client

```php
use Pobo\Sdk\PoboClient;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\LocalizedString;

$client = new PoboClient(
    apiToken: 'your-api-token',
    baseUrl: 'https://api.pobo.space', // optional
    timeout: 30 // optional, in seconds
);
```

### Import Parameters

Import parameters first (no dependencies).

```php
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\ParameterValue;

$parameter = new Parameter(
    id: 1,
    name: 'Color',
    values: [
        new ParameterValue(id: 1, value: 'Red'),
        new ParameterValue(id: 2, value: 'Blue'),
        new ParameterValue(id: 3, value: 'Green'),
    ],
);

$result = $client->importParameters([$parameter]);
echo sprintf('Values imported: %d', $result->valuesImported);
```

### Import Categories

Import categories second (no dependencies).

```php
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;

$category = new Category(
    id: 'CAT-001',
    isVisible: true,
    name: LocalizedString::create('Electronics'),
    url: LocalizedString::create('https://example.com/electronics'),
    description: LocalizedString::create('<p>All electronics</p>'),
);

$result = $client->importCategories([$category]);
```

### Import Products

Import products last (depends on categories and parameters IDs).

```php
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

// Using DTO objects
$product = new Product(
    id: 'PROD-001',
    isVisible: true,
    name: LocalizedString::create('iPhone 15')
        ->withTranslation(Language::SK, 'iPhone 15')
        ->withTranslation(Language::EN, 'iPhone 15'),
    url: LocalizedString::create('https://example.com/iphone-15')
        ->withTranslation(Language::SK, 'https://example.com/sk/iphone-15'),
    shortDescription: LocalizedString::create('Latest iPhone model'),
    images: [
        'https://example.com/images/iphone-1.jpg',
        'https://example.com/images/iphone-2.jpg',
    ],
    categoriesIds: ['CAT-001', 'CAT-002'],
    parametersIds: [1, 2, 3],
);

$result = $client->importProducts([$product]);

// Or using arrays
$result = $client->importProducts([
    [
        'id' => 'PROD-002',
        'is_visible' => true,
        'name' => ['default' => 'Samsung Galaxy S24'],
        'url' => ['default' => 'https://example.com/samsung-s24'],
    ],
]);

if ($result->hasErrors() === true) {
    foreach ($result->errors as $error) {
        echo sprintf('Error at index %d: %s', $error['index'], implode(', ', $error['errors']));
    }
}

echo sprintf('Imported: %d, Updated: %d', $result->imported, $result->updated);
```

### Export Products

```php
// Get single page
$response = $client->getProducts(page: 1, perPage: 50);

foreach ($response->data as $product) {
    echo sprintf("%s: %s\n", $product->id, $product->name->getDefault());
}

echo sprintf('Page %d of %d', $response->currentPage, $response->getTotalPages());

// Iterate through all products (handles pagination automatically)
foreach ($client->iterateProducts() as $product) {
    echo sprintf("%s: %s\n", $product->id, $product->name->getDefault());
}

// Filter by last update time
$since = new DateTime('2024-01-01 00:00:00');
$response = $client->getProducts(lastUpdateFrom: $since);

// Filter only edited products
$response = $client->getProducts(isEdited: true);
```

### Export Categories

```php
// Get all categories
$response = $client->getCategories();

foreach ($response->data as $category) {
    echo sprintf("%s: %s\n", $category->id, $category->name->getDefault());
}

// Iterate through all categories
foreach ($client->iterateCategories() as $category) {
    // Process category
}
```

## Webhook Handler

### Basic Usage

```php
use Pobo\Sdk\WebhookHandler;
use Pobo\Sdk\Enum\WebhookEvent;
use Pobo\Sdk\Exception\WebhookException;

$handler = new WebhookHandler(webhookSecret: 'your-webhook-secret');

try {
    // Handle from global PHP variables
    $payload = $handler->handleFromGlobals();

    // Or handle manually
    $payload = $handler->handle(
        payload: file_get_contents('php://input'),
        signature: $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? ''
    );

    match ($payload->event) {
        WebhookEvent::PRODUCTS_UPDATE => syncProducts($client),
        WebhookEvent::CATEGORIES_UPDATE => syncCategories($client),
    };

    http_response_code(200);
    echo json_encode(['status' => 'ok']);

} catch (WebhookException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
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
    // Local validation error (e.g., too many items)
    echo sprintf('Validation error: %s', $e->getMessage());
    print_r($e->errors);
} catch (ApiException $e) {
    // API error (4xx, 5xx)
    echo sprintf('API error (%d): %s', $e->httpCode, $e->getMessage());
    print_r($e->responseBody);
}
```

## Localized Strings

The SDK uses `LocalizedString` for multi-language support:

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

### Supported Languages

| Code | Language |
|------|----------|
| `default` | Default (required) |
| `cs` | Czech |
| `sk` | Slovak |
| `en` | English |
| `de` | German |
| `pl` | Polish |
| `hu` | Hungarian |

## Limits

| Limit | Value |
|-------|-------|
| Max items per import request | 100 |
| Max items per export page | 100 |
| Product/Category ID length | 255 chars |
| Name length | 250 chars |
| URL length | 255 chars |
| Image URL length | 650 chars |
| Description length | 65,000 chars |
| SEO description length | 500 chars |

## License

MIT License

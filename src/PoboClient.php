<?php

declare(strict_types=1);

namespace Pobo\Sdk;

use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\DeleteResult;
use Pobo\Sdk\DTO\ImportResult;
use Pobo\Sdk\DTO\PaginatedResponse;
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\IncludeContent;
use Pobo\Sdk\Enum\Language;
use Pobo\Sdk\Exception\ApiException;
use Pobo\Sdk\Exception\ValidationException;

class PoboClient
{
    private const DEFAULT_BASE_URL = 'https://api.pobo.space';
    private const MAX_BULK_ITEMS = 100;
    private const DEFAULT_TIMEOUT = 30;

    public function __construct(
        private readonly string $apiToken,
        private readonly string $baseUrl = self::DEFAULT_BASE_URL,
        private readonly int $timeout = self::DEFAULT_TIMEOUT,
    ) {
    }

    /**
     * @param array<Product|array<string, mixed>> $products
     * @throws ValidationException
     * @throws ApiException
     */
    public function importProducts(array $products): ImportResult
    {
        $this->validateBulkSize($products);

        $payload = array_map(
            fn($product) => $product instanceof Product ? $product->toArray() : $product,
            $products
        );

        $response = $this->request('POST', '/api/v2/rest/products', $payload);
        return ImportResult::fromArray($response);
    }

    /**
     * @param array<Category|array<string, mixed>> $categories
     * @throws ValidationException
     * @throws ApiException
     */
    public function importCategories(array $categories): ImportResult
    {
        $this->validateBulkSize($categories);

        $payload = array_map(
            fn($category) => $category instanceof Category ? $category->toArray() : $category,
            $categories
        );

        $response = $this->request('POST', '/api/v2/rest/categories', $payload);
        return ImportResult::fromArray($response);
    }

    /**
     * @param array<Parameter|array<string, mixed>> $parameters
     * @throws ValidationException
     * @throws ApiException
     */
    public function importParameters(array $parameters): ImportResult
    {
        $this->validateBulkSize($parameters);

        $payload = array_map(
            fn($parameter) => $parameter instanceof Parameter ? $parameter->toArray() : $parameter,
            $parameters
        );

        $response = $this->request('POST', '/api/v2/rest/parameters', $payload);
        return ImportResult::fromArray($response);
    }

    /**
     * @param array<Blog|array<string, mixed>> $blogs
     * @throws ValidationException
     * @throws ApiException
     */
    public function importBlogs(array $blogs): ImportResult
    {
        $this->validateBulkSize($blogs);

        $payload = array_map(
            fn($blog) => $blog instanceof Blog ? $blog->toArray() : $blog,
            $blogs
        );

        $response = $this->request('POST', '/api/v2/rest/blogs', $payload);
        return ImportResult::fromArray($response);
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include: IncludeContent::MARKETPLACE, IncludeContent::NESTED, IncludeContent::SITE_LINK, IncludeContent::RICH_SNIPPET
     * @param array<Language|string>|null $lang Languages to include in response. null = only default, [Language::ALL] = all languages
     * @return PaginatedResponse<Product>
     * @throws ApiException
     */
    public function getProducts(
        ?int $page = null,
        ?int $perPage = null,
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): PaginatedResponse {
        $query = $this->buildQueryParams($page, $perPage, $lastUpdateFrom, $isEdited, $include, $lang);
        $response = $this->request('GET', '/api/v2/rest/products' . $query);
        return PaginatedResponse::fromArray($response, Product::class);
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include: IncludeContent::MARKETPLACE, IncludeContent::NESTED, IncludeContent::RICH_SNIPPET
     * @param array<Language|string>|null $lang Languages to include in response. null = only default, [Language::ALL] = all languages
     * @return PaginatedResponse<Category>
     * @throws ApiException
     */
    public function getCategories(
        ?int $page = null,
        ?int $perPage = null,
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): PaginatedResponse {
        $query = $this->buildQueryParams($page, $perPage, $lastUpdateFrom, $isEdited, $include, $lang);
        $response = $this->request('GET', '/api/v2/rest/categories' . $query);
        return PaginatedResponse::fromArray($response, Category::class);
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include: IncludeContent::MARKETPLACE, IncludeContent::NESTED, IncludeContent::SITE_LINK, IncludeContent::RICH_SNIPPET
     * @param array<Language|string>|null $lang Languages to include in response. null = only default, [Language::ALL] = all languages
     * @return PaginatedResponse<Blog>
     * @throws ApiException
     */
    public function getBlogs(
        ?int $page = null,
        ?int $perPage = null,
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): PaginatedResponse {
        $query = $this->buildQueryParams($page, $perPage, $lastUpdateFrom, $isEdited, $include, $lang);
        $response = $this->request('GET', '/api/v2/rest/blogs' . $query);
        return PaginatedResponse::fromArray($response, Blog::class);
    }

    /**
     * @param array<string> $ids
     * @throws ValidationException
     * @throws ApiException
     */
    public function deleteProducts(array $ids): DeleteResult
    {
        $this->validateBulkSize($ids);

        $payload = array_map(fn(string $id) => ['id' => $id], $ids);

        $response = $this->request('DELETE', '/api/v2/rest/products', $payload);
        return DeleteResult::fromArray($response);
    }

    /**
     * @param array<string> $ids
     * @throws ValidationException
     * @throws ApiException
     */
    public function deleteCategories(array $ids): DeleteResult
    {
        $this->validateBulkSize($ids);

        $payload = array_map(fn(string $id) => ['id' => $id], $ids);

        $response = $this->request('DELETE', '/api/v2/rest/categories', $payload);
        return DeleteResult::fromArray($response);
    }

    /**
     * @param array<string> $ids
     * @throws ValidationException
     * @throws ApiException
     */
    public function deleteBlogs(array $ids): DeleteResult
    {
        $this->validateBulkSize($ids);

        $payload = array_map(fn(string $id) => ['id' => $id], $ids);

        $response = $this->request('DELETE', '/api/v2/rest/blogs', $payload);
        return DeleteResult::fromArray($response);
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include
     * @param array<Language|string>|null $lang Languages to include in response
     * @return \Generator<Product>
     * @throws ApiException
     */
    public function iterateProducts(
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): \Generator {
        $page = 1;

        do {
            $response = $this->getProducts($page, self::MAX_BULK_ITEMS, $lastUpdateFrom, $isEdited, $include, $lang);

            foreach ($response->data as $product) {
                yield $product;
            }

            $page++;
        } while ($response->hasMorePages());
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include
     * @param array<Language|string>|null $lang Languages to include in response
     * @return \Generator<Category>
     * @throws ApiException
     */
    public function iterateCategories(
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): \Generator {
        $page = 1;

        do {
            $response = $this->getCategories($page, self::MAX_BULK_ITEMS, $lastUpdateFrom, $isEdited, $include, $lang);

            foreach ($response->data as $category) {
                yield $category;
            }

            $page++;
        } while ($response->hasMorePages());
    }

    /**
     * @param array<IncludeContent|string>|null $include Optional content to include
     * @param array<Language|string>|null $lang Languages to include in response
     * @return \Generator<Blog>
     * @throws ApiException
     */
    public function iterateBlogs(
        ?\DateTimeInterface $lastUpdateFrom = null,
        ?bool $isEdited = null,
        ?array $include = null,
        ?array $lang = null,
    ): \Generator {
        $page = 1;

        do {
            $response = $this->getBlogs($page, self::MAX_BULK_ITEMS, $lastUpdateFrom, $isEdited, $include, $lang);

            foreach ($response->data as $blog) {
                yield $blog;
            }

            $page++;
        } while ($response->hasMorePages());
    }

    /**
     * @param array<mixed> $items
     * @throws ValidationException
     */
    private function validateBulkSize(array $items): void
    {
        if ($items === []) {
            throw ValidationException::emptyPayload();
        }

        $count = count($items);
        if ($count > self::MAX_BULK_ITEMS) {
            throw ValidationException::tooManyItems($count, self::MAX_BULK_ITEMS);
        }
    }

    /**
     * @param array<IncludeContent|string>|null $include
     * @param array<Language|string>|null $lang
     */
    private function buildQueryParams(
        ?int $page,
        ?int $perPage,
        ?\DateTimeInterface $lastUpdateFrom,
        ?bool $isEdited,
        ?array $include = null,
        ?array $lang = null,
    ): string {
        $params = [];

        if ($page !== null) {
            $params['page'] = $page;
        }

        if ($perPage !== null) {
            $params['per_page'] = min($perPage, self::MAX_BULK_ITEMS);
        }

        if ($lastUpdateFrom !== null) {
            $params['last_update_time_from'] = $lastUpdateFrom->format('Y-m-d H:i:s');
        }

        if ($isEdited !== null) {
            $params['is_edited'] = $isEdited === true ? 'true' : 'false';
        }

        if ($include !== null && $include !== []) {
            $values = array_map(
                fn(IncludeContent|string $item) => $item instanceof IncludeContent ? $item->value : $item,
                $include,
            );
            $params['include'] = implode(',', $values);
        }

        if ($lang !== null && $lang !== []) {
            $values = array_map(
                fn(Language|string $item) => $item instanceof Language ? $item->value : $item,
                $lang,
            );
            $params['lang'] = implode(',', $values);
        }

        return $params === [] ? '' : sprintf('?%s', http_build_query($params));
    }

    /**
     * @param array<mixed>|null $data
     * @return array<string, mixed>
     * @throws ApiException
     */
    private function request(string $method, string $endpoint, ?array $data = null): array
    {
        $url = rtrim($this->baseUrl, '/') . $endpoint;

        $ch = curl_init($url);

        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if (!is_string($response)) {
            throw new ApiException(sprintf('cURL error: %s', $error), 0);
        }

        /** @var array<string, mixed>|null $body */
        $body = json_decode($response, true);

        if ($httpCode === 401) {
            throw ApiException::unauthorized();
        }

        if ($httpCode >= 400) {
            throw ApiException::fromResponse($httpCode, $body);
        }

        return $body ?? [];
    }
}

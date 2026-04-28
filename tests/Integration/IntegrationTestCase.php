<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\PoboClient;

abstract class IntegrationTestCase extends TestCase
{
    protected PoboClient $client;

    /**
     * Unique prefix used for IDs created by a single test run.
     * Combines GitHub run ID (when in CI) with a random suffix so concurrent
     * runs do not collide on the test e-shop.
     */
    protected string $idPrefix;

    /** @var array<string> */
    private array $productIdsToCleanup = [];

    /** @var array<string> */
    private array $categoryIdsToCleanup = [];

    /** @var array<string> */
    private array $blogIdsToCleanup = [];

    protected function setUp(): void
    {
        $token = getenv('POBO_API_TOKEN');

        if ($token === false || $token === '') {
            self::markTestSkipped('Integration tests require POBO_API_TOKEN environment variable.');
        }

        $baseUrl = getenv('POBO_BASE_URL');
        if ($baseUrl === false || $baseUrl === '') {
            $baseUrl = 'https://api.pobo.space';
        }

        $this->client = new PoboClient(
            apiToken: $token,
            baseUrl: $baseUrl,
            timeout: 60,
        );

        $runId = getenv('GITHUB_RUN_ID');
        if ($runId === false || $runId === '') {
            $runId = (string) getmypid();
        }
        $this->idPrefix = sprintf('CI-%s-%s', $runId, bin2hex(random_bytes(3)));
    }

    protected function tearDown(): void
    {
        if ($this->productIdsToCleanup !== []) {
            try {
                $this->client->deleteProducts($this->productIdsToCleanup);
            } catch (\Throwable) {
                // Cleanup is best-effort; don't mask the actual test failure.
            }
        }

        if ($this->categoryIdsToCleanup !== []) {
            try {
                $this->client->deleteCategories($this->categoryIdsToCleanup);
            } catch (\Throwable) {
            }
        }

        if ($this->blogIdsToCleanup !== []) {
            try {
                $this->client->deleteBlogs($this->blogIdsToCleanup);
            } catch (\Throwable) {
            }
        }
    }

    protected function trackProduct(string $id): void
    {
        $this->productIdsToCleanup[] = $id;
    }

    protected function untrackProduct(string $id): void
    {
        $this->productIdsToCleanup = array_values(array_filter(
            $this->productIdsToCleanup,
            fn(string $existing) => $existing !== $id,
        ));
    }

    protected function trackCategory(string $id): void
    {
        $this->categoryIdsToCleanup[] = $id;
    }

    protected function untrackCategory(string $id): void
    {
        $this->categoryIdsToCleanup = array_values(array_filter(
            $this->categoryIdsToCleanup,
            fn(string $existing) => $existing !== $id,
        ));
    }

    protected function trackBlog(string $id): void
    {
        $this->blogIdsToCleanup[] = $id;
    }

    protected function untrackBlog(string $id): void
    {
        $this->blogIdsToCleanup = array_values(array_filter(
            $this->blogIdsToCleanup,
            fn(string $existing) => $existing !== $id,
        ));
    }

    protected function uniqueId(string $kind): string
    {
        return sprintf('%s-%s-%s', $this->idPrefix, $kind, bin2hex(random_bytes(2)));
    }
}

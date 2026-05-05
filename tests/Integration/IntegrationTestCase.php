<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Integration;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Blog;
use Pobo\Sdk\DTO\Brand;
use Pobo\Sdk\DTO\Category;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\DTO\Product;
use Pobo\Sdk\Enum\Language;
use Pobo\Sdk\PoboClient;

abstract class IntegrationTestCase extends TestCase
{
    protected PoboClient $client;

    protected FakerGenerator $faker;

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

    /** @var array<string> */
    private array $brandIdsToCleanup = [];

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

        $this->faker = FakerFactory::create('en_US');
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

        if ($this->brandIdsToCleanup !== []) {
            try {
                $this->client->deleteBrands($this->brandIdsToCleanup);
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

    protected function trackBrand(string $id): void
    {
        $this->brandIdsToCleanup[] = $id;
    }

    protected function untrackBrand(string $id): void
    {
        $this->brandIdsToCleanup = array_values(array_filter(
            $this->brandIdsToCleanup,
            fn(string $existing) => $existing !== $id,
        ));
    }

    protected function uniqueId(string $kind): string
    {
        return sprintf('%s-%s-%s', $this->idPrefix, $kind, bin2hex(random_bytes(2)));
    }

    /**
     * Build a Product with realistic faker content. If $id is null, a new unique
     * ID is generated and tracked for cleanup. If $id is provided, the caller is
     * responsible for tracking it (typical for update tests reusing existing IDs).
     */
    protected function makeProduct(?string $id = null): Product
    {
        if ($id === null) {
            $id = $this->uniqueId('prod');
            $this->trackProduct($id);
        }

        $nameEn = $this->faker->unique()->words(3, true);
        $nameCs = sprintf('Produkt %s', $this->faker->unique()->word());
        $slug = $this->faker->slug(2);

        return new Product(
            id: $id,
            isVisible: true,
            name: LocalizedString::create($nameEn)->withTranslation(Language::CS, $nameCs),
            url: LocalizedString::create(sprintf('https://example.com/%s', $slug))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $slug)),
            shortDescription: LocalizedString::create($this->faker->sentence(8))
                ->withTranslation(Language::CS, $this->faker->sentence(8)),
            description: LocalizedString::create(sprintf('<p>%s</p>', $this->faker->paragraph(3)))
                ->withTranslation(Language::CS, sprintf('<p>%s</p>', $this->faker->paragraph(3))),
        );
    }

    /**
     * Build a Category with realistic faker content. See {@see makeProduct()} for ID behavior.
     */
    protected function makeCategory(?string $id = null): Category
    {
        if ($id === null) {
            $id = $this->uniqueId('cat');
            $this->trackCategory($id);
        }

        $nameEn = $this->faker->unique()->words(2, true);
        $nameCs = sprintf('Kategorie %s', $this->faker->unique()->word());
        $slug = $this->faker->slug(2);

        return new Category(
            id: $id,
            isVisible: true,
            name: LocalizedString::create($nameEn)->withTranslation(Language::CS, $nameCs),
            url: LocalizedString::create(sprintf('https://example.com/%s', $slug))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/%s', $slug)),
            description: LocalizedString::create(sprintf('<p>%s</p>', $this->faker->paragraph(2)))
                ->withTranslation(Language::CS, sprintf('<p>%s</p>', $this->faker->paragraph(2))),
        );
    }

    /**
     * Build a Blog with realistic faker content. See {@see makeProduct()} for ID behavior.
     */
    protected function makeBlog(?string $id = null): Blog
    {
        if ($id === null) {
            $id = $this->uniqueId('blog');
            $this->trackBlog($id);
        }

        $titleEn = $this->faker->unique()->sentence(4);
        $titleCs = sprintf('Článek %s', $this->faker->unique()->word());
        $slug = $this->faker->slug(3);

        return new Blog(
            id: $id,
            isVisible: true,
            name: LocalizedString::create($titleEn)->withTranslation(Language::CS, $titleCs),
            url: LocalizedString::create(sprintf('https://example.com/blog/%s', $slug))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/blog/%s', $slug)),
            category: $this->faker->randomElement(['news', 'tips', 'reviews', 'guides']),
            description: LocalizedString::create(sprintf('<p>%s</p>', $this->faker->paragraph(4)))
                ->withTranslation(Language::CS, sprintf('<p>%s</p>', $this->faker->paragraph(4))),
        );
    }

    /**
     * Build a Brand with realistic faker content. See {@see makeProduct()} for ID behavior.
     */
    protected function makeBrand(?string $id = null): Brand
    {
        if ($id === null) {
            $id = $this->uniqueId('brand');
            $this->trackBrand($id);
        }

        $nameEn = $this->faker->unique()->company();
        $nameCs = sprintf('Značka %s', $this->faker->unique()->word());
        $slug = $this->faker->slug(2);

        return new Brand(
            id: $id,
            isVisible: true,
            name: LocalizedString::create($nameEn)->withTranslation(Language::CS, $nameCs),
            url: LocalizedString::create(sprintf('https://example.com/znacky/%s', $slug))
                ->withTranslation(Language::CS, sprintf('https://example.com/cs/znacky/%s', $slug)),
            description: LocalizedString::create(sprintf('<p>%s</p>', $this->faker->paragraph(2)))
                ->withTranslation(Language::CS, sprintf('<p>%s</p>', $this->faker->paragraph(2))),
        );
    }
}

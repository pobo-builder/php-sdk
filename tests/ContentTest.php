<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Content;
use Pobo\Sdk\Enum\Language;

final class ContentTest extends TestCase
{
    public function testCreateContentFromArray(): void
    {
        $data = [
            'html' => [
                'cs' => '<div>Czech HTML</div>',
                'sk' => '<div>Slovak HTML</div>',
                'en' => '<div>English HTML</div>',
            ],
            'marketplace' => [
                'cs' => '<div>Czech Marketplace</div>',
                'sk' => '<div>Slovak Marketplace</div>',
            ],
        ];

        $content = Content::fromArray($data);

        $this->assertSame('<div>Czech HTML</div>', $content->getHtml(Language::CS));
        $this->assertSame('<div>Slovak HTML</div>', $content->getHtml(Language::SK));
        $this->assertSame('<div>English HTML</div>', $content->getHtml(Language::EN));
        $this->assertSame('<div>Czech Marketplace</div>', $content->getMarketplace(Language::CS));
        $this->assertSame('<div>Slovak Marketplace</div>', $content->getMarketplace(Language::SK));
    }

    public function testGetHtmlReturnsNullForMissingLanguage(): void
    {
        $content = Content::fromArray([
            'html' => ['cs' => '<div>Czech</div>'],
            'marketplace' => [],
        ]);

        $this->assertNull($content->getHtml(Language::DE));
        $this->assertNull($content->getHtml(Language::PL));
    }

    public function testGetMarketplaceReturnsNullForMissingLanguage(): void
    {
        $content = Content::fromArray([
            'html' => [],
            'marketplace' => ['cs' => '<div>Czech</div>'],
        ]);

        $this->assertNull($content->getMarketplace(Language::EN));
        $this->assertNull($content->getMarketplace(Language::HU));
    }

    public function testGetHtmlDefault(): void
    {
        $content = Content::fromArray([
            'html' => [
                'default' => '<div>Default HTML</div>',
                'cs' => '<div>Czech HTML</div>',
            ],
            'marketplace' => [],
        ]);

        $this->assertSame('<div>Default HTML</div>', $content->getHtmlDefault());
    }

    public function testGetHtmlDefaultFallsBackToCs(): void
    {
        $content = Content::fromArray([
            'html' => [
                'cs' => '<div>Czech HTML</div>',
            ],
            'marketplace' => [],
        ]);

        $this->assertSame('<div>Czech HTML</div>', $content->getHtmlDefault());
    }

    public function testGetMarketplaceDefault(): void
    {
        $content = Content::fromArray([
            'html' => [],
            'marketplace' => [
                'default' => '<div>Default Marketplace</div>',
                'cs' => '<div>Czech Marketplace</div>',
            ],
        ]);

        $this->assertSame('<div>Default Marketplace</div>', $content->getMarketplaceDefault());
    }

    public function testGetMarketplaceDefaultFallsBackToCs(): void
    {
        $content = Content::fromArray([
            'html' => [],
            'marketplace' => [
                'cs' => '<div>Czech Marketplace</div>',
            ],
        ]);

        $this->assertSame('<div>Czech Marketplace</div>', $content->getMarketplaceDefault());
    }

    public function testCreateContentWithNested(): void
    {
        $nested = [
            [['id' => 2, 'class' => 'empty', 'tag' => 'div', 'children' => []]],
            [['id' => 5, 'class' => 'text', 'tag' => 'p', 'children' => []]],
        ];

        $content = Content::fromArray([
            'html' => ['cs' => '<div>Czech</div>'],
            'marketplace' => [],
            'nested' => $nested,
        ]);

        $this->assertSame($nested, $content->getNested());
        $this->assertCount(2, $content->nested);
    }

    public function testNestedIsEmptyByDefault(): void
    {
        $content = Content::fromArray([
            'html' => ['cs' => '<div>Czech</div>'],
        ]);

        $this->assertSame([], $content->getNested());
        $this->assertSame([], $content->nested);
    }

    public function testToArray(): void
    {
        $content = new Content(
            html: ['cs' => '<div>Czech</div>', 'en' => '<div>English</div>'],
            marketplace: ['cs' => '<div>Czech MP</div>'],
        );

        $array = $content->toArray();

        $this->assertArrayHasKey('html', $array);
        $this->assertArrayHasKey('marketplace', $array);
        $this->assertArrayNotHasKey('nested', $array);
        $this->assertSame('<div>Czech</div>', $array['html']['cs']);
        $this->assertSame('<div>English</div>', $array['html']['en']);
        $this->assertSame('<div>Czech MP</div>', $array['marketplace']['cs']);
    }

    public function testToArrayWithNested(): void
    {
        $nested = [[['id' => 1, 'class' => 'empty', 'tag' => 'div', 'children' => []]]];

        $content = new Content(
            html: ['cs' => '<div>Czech</div>'],
            marketplace: [],
            nested: $nested,
        );

        $array = $content->toArray();

        $this->assertArrayHasKey('nested', $array);
        $this->assertSame($nested, $array['nested']);
    }

    public function testEmptyContent(): void
    {
        $content = Content::fromArray([]);

        $this->assertSame([], $content->html);
        $this->assertSame([], $content->marketplace);
        $this->assertSame([], $content->nested);
        $this->assertNull($content->getHtmlDefault());
        $this->assertNull($content->getMarketplaceDefault());
    }

    public function testFromArrayToArrayRoundtrip(): void
    {
        $nested = [[['id' => 2, 'class' => 'empty', 'tag' => 'div', 'children' => []]]];

        $data = [
            'html' => [
                'default' => '<div>Default</div>',
                'cs' => '<div>Czech</div>',
                'sk' => '<div>Slovak</div>',
            ],
            'marketplace' => [
                'default' => '<div>Default MP</div>',
                'cs' => '<div>Czech MP</div>',
            ],
            'nested' => $nested,
        ];

        $content = Content::fromArray($data);
        $array = $content->toArray();

        $this->assertSame($data['html'], $array['html']);
        $this->assertSame($data['marketplace'], $array['marketplace']);
        $this->assertSame($data['nested'], $array['nested']);
    }

    public function testContentIsReadonly(): void
    {
        $content = new Content(
            html: ['cs' => '<div>Test</div>'],
            marketplace: ['cs' => '<div>Test MP</div>'],
        );

        $this->assertSame(['cs' => '<div>Test</div>'], $content->html);
        $this->assertSame(['cs' => '<div>Test MP</div>'], $content->marketplace);
        $this->assertSame([], $content->nested);
    }
}

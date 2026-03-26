<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\RichSnippet;
use Pobo\Sdk\Enum\Language;

final class RichSnippetTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'html' => [
                'default' => '<script type="application/ld+json">{"@type":"FAQPage"}</script>',
                'cs' => '<script type="application/ld+json">{"@type":"FAQPage"}</script>',
            ],
            'json' => [
                'default' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => [
                        [
                            '@type' => 'Question',
                            'name' => 'Otázka?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Odpověď.',
                            ],
                        ],
                    ],
                ],
                'cs' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => [],
                ],
            ],
        ];

        $richSnippet = RichSnippet::fromArray($data);

        $this->assertStringContainsString('FAQPage', $richSnippet->getHtml(Language::DEFAULT));
        $this->assertNull($richSnippet->getHtml(Language::SK));

        $defaultJson = $richSnippet->getJson(Language::DEFAULT);
        $this->assertNotNull($defaultJson);
        $this->assertSame('FAQPage', $defaultJson['@type']);
        $this->assertCount(1, $defaultJson['mainEntity']);

        $csJson = $richSnippet->getJson(Language::CS);
        $this->assertNotNull($csJson);
        $this->assertSame([], $csJson['mainEntity']);

        $this->assertNull($richSnippet->getJson(Language::SK));
    }

    public function testFromArrayEmpty(): void
    {
        $richSnippet = RichSnippet::fromArray([]);

        $this->assertSame([], $richSnippet->html);
        $this->assertSame([], $richSnippet->json);
        $this->assertNull($richSnippet->getHtml(Language::DEFAULT));
        $this->assertNull($richSnippet->getJson(Language::DEFAULT));
    }
}

<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\SiteLink;
use Pobo\Sdk\DTO\SiteLinkItem;
use Pobo\Sdk\Enum\Language;

final class SiteLinkTest extends TestCase
{
    public function testFromArray(): void
    {
        $data = [
            'html' => [
                'default' => '<div id="pobo-site-link"><nav><a href="#nadpis">Nadpis</a></nav></div>',
                'cs' => '<div id="pobo-site-link"><nav><a href="#nadpis">Nadpis</a></nav></div>',
            ],
            'list' => [
                'default' => [
                    ['heading' => 'Nadpis', 'slug' => 'nadpis'],
                    ['heading' => 'Druhý nadpis', 'slug' => 'druhy-nadpis'],
                ],
                'cs' => [
                    ['heading' => 'Nadpis', 'slug' => 'nadpis'],
                ],
            ],
        ];

        $siteLink = SiteLink::fromArray($data);

        $this->assertSame($data['html']['default'], $siteLink->getHtml(Language::DEFAULT));
        $this->assertSame($data['html']['cs'], $siteLink->getHtml(Language::CS));
        $this->assertNull($siteLink->getHtml(Language::SK));

        $defaultList = $siteLink->getList(Language::DEFAULT);
        $this->assertNotNull($defaultList);
        $this->assertCount(2, $defaultList);
        $this->assertInstanceOf(SiteLinkItem::class, $defaultList[0]);
        $this->assertSame('Nadpis', $defaultList[0]->heading);
        $this->assertSame('nadpis', $defaultList[0]->slug);
        $this->assertSame('Druhý nadpis', $defaultList[1]->heading);
        $this->assertSame('druhy-nadpis', $defaultList[1]->slug);

        $csList = $siteLink->getList(Language::CS);
        $this->assertNotNull($csList);
        $this->assertCount(1, $csList);

        $this->assertNull($siteLink->getList(Language::SK));
    }

    public function testFromArrayEmpty(): void
    {
        $siteLink = SiteLink::fromArray([]);

        $this->assertSame([], $siteLink->html);
        $this->assertSame([], $siteLink->list);
        $this->assertNull($siteLink->getHtml(Language::DEFAULT));
        $this->assertNull($siteLink->getList(Language::DEFAULT));
    }

    public function testSiteLinkItemFromArray(): void
    {
        $item = SiteLinkItem::fromArray([
            'heading' => 'Test Heading',
            'slug' => 'test-heading',
        ]);

        $this->assertSame('Test Heading', $item->heading);
        $this->assertSame('test-heading', $item->slug);
    }

    public function testSiteLinkItemToArray(): void
    {
        $item = new SiteLinkItem(heading: 'Test', slug: 'test');

        $this->assertSame(['heading' => 'Test', 'slug' => 'test'], $item->toArray());
    }
}

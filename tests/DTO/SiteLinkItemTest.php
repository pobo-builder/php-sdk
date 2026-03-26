<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\SiteLinkItem;

final class SiteLinkItemTest extends TestCase
{
    public function testFromArray(): void
    {
        $item = SiteLinkItem::fromArray([
            'heading' => 'Introduction',
            'slug' => 'introduction',
        ]);

        $this->assertSame('Introduction', $item->heading);
        $this->assertSame('introduction', $item->slug);
    }

    public function testToArray(): void
    {
        $item = new SiteLinkItem(
            heading: 'Features',
            slug: 'features',
        );

        $this->assertSame([
            'heading' => 'Features',
            'slug' => 'features',
        ], $item->toArray());
    }

    public function testRoundtrip(): void
    {
        $data = ['heading' => 'FAQ', 'slug' => 'faq'];
        $item = SiteLinkItem::fromArray($data);

        $this->assertSame($data, $item->toArray());
    }
}

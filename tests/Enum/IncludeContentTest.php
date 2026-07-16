<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Enum\IncludeContent;

final class IncludeContentTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertSame('marketplace', IncludeContent::MARKETPLACE->value);
        $this->assertSame('nested', IncludeContent::NESTED->value);
        $this->assertSame('site_link', IncludeContent::SITE_LINK->value);
        $this->assertSame('rich_snippet', IncludeContent::RICH_SNIPPET->value);
        $this->assertSame('variant', IncludeContent::VARIANT->value);
    }

    public function testValues(): void
    {
        $values = IncludeContent::values();

        $this->assertContains('marketplace', $values);
        $this->assertContains('nested', $values);
        $this->assertContains('site_link', $values);
        $this->assertContains('rich_snippet', $values);
        $this->assertContains('variant', $values);
        $this->assertCount(5, $values);
    }

    public function testIsValidReturnsTrue(): void
    {
        $this->assertTrue(IncludeContent::isValid('marketplace'));
        $this->assertTrue(IncludeContent::isValid('nested'));
        $this->assertTrue(IncludeContent::isValid('site_link'));
        $this->assertTrue(IncludeContent::isValid('rich_snippet'));
        $this->assertTrue(IncludeContent::isValid('variant'));
    }

    public function testIsValidReturnsFalse(): void
    {
        $this->assertFalse(IncludeContent::isValid('html'));
        $this->assertFalse(IncludeContent::isValid(''));
        $this->assertFalse(IncludeContent::isValid('invalid'));
        $this->assertFalse(IncludeContent::isValid('MARKETPLACE'));
        $this->assertFalse(IncludeContent::isValid('NESTED'));
        $this->assertFalse(IncludeContent::isValid('variants'));
    }

    public function testCanBeUsedInArray(): void
    {
        $include = [IncludeContent::MARKETPLACE, IncludeContent::NESTED, IncludeContent::SITE_LINK, IncludeContent::RICH_SNIPPET];

        $values = array_map(fn(IncludeContent $item) => $item->value, $include);

        $this->assertSame(['marketplace', 'nested', 'site_link', 'rich_snippet'], $values);
    }
}

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
    }

    public function testValues(): void
    {
        $values = IncludeContent::values();

        $this->assertContains('marketplace', $values);
        $this->assertContains('nested', $values);
        $this->assertCount(2, $values);
    }

    public function testIsValidReturnsTrue(): void
    {
        $this->assertTrue(IncludeContent::isValid('marketplace'));
        $this->assertTrue(IncludeContent::isValid('nested'));
    }

    public function testIsValidReturnsFalse(): void
    {
        $this->assertFalse(IncludeContent::isValid('html'));
        $this->assertFalse(IncludeContent::isValid(''));
        $this->assertFalse(IncludeContent::isValid('invalid'));
        $this->assertFalse(IncludeContent::isValid('MARKETPLACE'));
        $this->assertFalse(IncludeContent::isValid('NESTED'));
    }

    public function testCanBeUsedInArray(): void
    {
        $include = [IncludeContent::MARKETPLACE, IncludeContent::NESTED];

        $values = array_map(fn(IncludeContent $item) => $item->value, $include);

        $this->assertSame(['marketplace', 'nested'], $values);
    }
}

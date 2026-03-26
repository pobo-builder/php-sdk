<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Enum\Language;

final class LanguageTest extends TestCase
{
    public function testAllCasesExist(): void
    {
        $this->assertSame('default', Language::DEFAULT->value);
        $this->assertSame('cs', Language::CS->value);
        $this->assertSame('sk', Language::SK->value);
        $this->assertSame('en', Language::EN->value);
        $this->assertSame('de', Language::DE->value);
        $this->assertSame('pl', Language::PL->value);
        $this->assertSame('hu', Language::HU->value);
    }

    public function testAllCase(): void
    {
        $this->assertSame('all', Language::ALL->value);
    }

    public function testValues(): void
    {
        $values = Language::values();

        $this->assertContains('default', $values);
        $this->assertContains('cs', $values);
        $this->assertContains('sk', $values);
        $this->assertContains('en', $values);
        $this->assertContains('de', $values);
        $this->assertContains('pl', $values);
        $this->assertContains('hu', $values);
        $this->assertContains('all', $values);
        $this->assertCount(8, $values);
    }

    public function testIsValidReturnsTrue(): void
    {
        $this->assertTrue(Language::isValid('default'));
        $this->assertTrue(Language::isValid('cs'));
        $this->assertTrue(Language::isValid('sk'));
        $this->assertTrue(Language::isValid('en'));
        $this->assertTrue(Language::isValid('all'));
    }

    public function testIsValidReturnsFalse(): void
    {
        $this->assertFalse(Language::isValid('fr'));
        $this->assertFalse(Language::isValid(''));
        $this->assertFalse(Language::isValid('invalid'));
        $this->assertFalse(Language::isValid('DEFAULT'));
    }
}

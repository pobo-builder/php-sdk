<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\LocalizedString;
use Pobo\Sdk\Enum\Language;

final class LocalizedStringTest extends TestCase
{
    public function testCreate(): void
    {
        $string = LocalizedString::create('Default value');

        $this->assertSame('Default value', $string->getDefault());
        $this->assertSame('Default value', $string->get(Language::DEFAULT));
    }

    public function testWithTranslation(): void
    {
        $string = LocalizedString::create('Default')
            ->withTranslation(Language::CS, 'Czech')
            ->withTranslation(Language::SK, 'Slovak');

        $this->assertSame('Default', $string->getDefault());
        $this->assertSame('Czech', $string->get(Language::CS));
        $this->assertSame('Slovak', $string->get(Language::SK));
        $this->assertNull($string->get(Language::EN));
    }

    public function testWithTranslationIsImmutable(): void
    {
        $original = LocalizedString::create('Default');
        $modified = $original->withTranslation(Language::CS, 'Czech');

        $this->assertNull($original->get(Language::CS));
        $this->assertSame('Czech', $modified->get(Language::CS));
    }

    public function testToArray(): void
    {
        $string = LocalizedString::create('Default')
            ->withTranslation(Language::CS, 'Czech')
            ->withTranslation(Language::SK, null);

        $expected = [
            'default' => 'Default',
            'cs' => 'Czech',
            'sk' => null,
        ];

        $this->assertSame($expected, $string->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'default' => 'Default',
            'cs' => 'Czech',
            'en' => 'English',
        ];

        $string = LocalizedString::fromArray($data);

        $this->assertSame('Default', $string->getDefault());
        $this->assertSame('Czech', $string->get(Language::CS));
        $this->assertSame('English', $string->get(Language::EN));
    }
}

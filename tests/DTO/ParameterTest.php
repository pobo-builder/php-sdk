<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\DTO;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\DTO\Parameter;
use Pobo\Sdk\DTO\ParameterValue;

final class ParameterTest extends TestCase
{
    public function testToArray(): void
    {
        $parameter = new Parameter(
            id: 1,
            name: 'Color',
            values: [
                new ParameterValue(id: 1, value: 'Red'),
                new ParameterValue(id: 2, value: 'Blue'),
            ],
        );

        $expected = [
            'id' => 1,
            'name' => 'Color',
            'values' => [
                ['id' => 1, 'value' => 'Red'],
                ['id' => 2, 'value' => 'Blue'],
            ],
        ];

        $this->assertSame($expected, $parameter->toArray());
    }

    public function testFromArray(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Size',
            'values' => [
                ['id' => 10, 'value' => 'Small'],
                ['id' => 20, 'value' => 'Medium'],
                ['id' => 30, 'value' => 'Large'],
            ],
        ];

        $parameter = Parameter::fromArray($data);

        $this->assertSame(1, $parameter->id);
        $this->assertSame('Size', $parameter->name);
        $this->assertCount(3, $parameter->values);
        $this->assertSame(10, $parameter->values[0]->id);
        $this->assertSame('Small', $parameter->values[0]->value);
        $this->assertSame(30, $parameter->values[2]->id);
        $this->assertSame('Large', $parameter->values[2]->value);
    }

    public function testFromArrayWithEmptyValues(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Empty',
        ];

        $parameter = Parameter::fromArray($data);

        $this->assertSame(1, $parameter->id);
        $this->assertSame('Empty', $parameter->name);
        $this->assertSame([], $parameter->values);
    }
}

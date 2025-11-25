<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat\Tests;

use JimmyAhalpara\ToonFormat\ToonFormat;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    public function testEncodeSimpleObject(): void
    {
        $data = ['name' => 'Alice', 'age' => 30];
        $result = ToonFormat::encode($data);

        $this->assertStringContainsString('name: Alice', $result);
        $this->assertStringContainsString('age: 30', $result);
    }

    public function testEncodePrimitiveArray(): void
    {
        $data = [1, 2, 3];
        $result = ToonFormat::encode($data);

        $this->assertEquals('[3]: 1,2,3', $result);
    }

    public function testEncodeTabularArray(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        $result = ToonFormat::encode($data);

        $this->assertStringContainsString('[2,]{id,name}:', $result);
        $this->assertStringContainsString('1,Alice', $result);
        $this->assertStringContainsString('2,Bob', $result);
    }

    public function testEncodeWithTabDelimiter(): void
    {
        $data = [1, 2, 3];
        $result = ToonFormat::encode($data, ['delimiter' => "\t"]);

        $this->assertStringContainsString("[3\t]:", $result);
        $this->assertStringContainsString("1\t2\t3", $result);
    }

    public function testEncodeWithLengthMarker(): void
    {
        $data = [1, 2, 3];
        $result = ToonFormat::encode($data, ['lengthMarker' => '#']);

        $this->assertStringContainsString('[#3]:', $result);
    }

    public function testEncodeNestedObject(): void
    {
        $data = [
            'user' => [
                'name' => 'Alice',
                'age' => 30,
            ],
        ];
        $result = ToonFormat::encode($data);

        $this->assertStringContainsString('user:', $result);
        $this->assertStringContainsString('name: Alice', $result);
        $this->assertStringContainsString('age: 30', $result);
    }

    public function testEncodeNull(): void
    {
        $result = ToonFormat::encode(null);
        $this->assertEquals('null', $result);
    }

    public function testEncodeBoolean(): void
    {
        $this->assertEquals('true', ToonFormat::encode(true));
        $this->assertEquals('false', ToonFormat::encode(false));
    }

    public function testEncodeString(): void
    {
        $this->assertEquals('hello', ToonFormat::encode('hello'));
        $this->assertEquals('hello world', ToonFormat::encode('hello world')); // Internal spaces are OK
        $this->assertEquals('" leading"', ToonFormat::encode(' leading')); // Leading space requires quotes
        $this->assertEquals('"trailing "', ToonFormat::encode('trailing ')); // Trailing space requires quotes
    }

    public function testEncodeEmptyArray(): void
    {
        $result = ToonFormat::encode([]);
        $this->assertEquals('[0]:', $result);
    }

    public function testEncodeEmptyObject(): void
    {
        // Empty stdClass normalizes to empty array, which looks like a list []
        // To explicitly create an empty object, use an associative array with no numeric keys
        $result = ToonFormat::encode(new \stdClass());

        // But actually, PHP's empty array [] is ambiguous - could be list or object
        // The encoder should treat empty stdClass specially
        // For now, let's test with an actual empty associative array
        $this->assertEquals('', $result);
    }
}

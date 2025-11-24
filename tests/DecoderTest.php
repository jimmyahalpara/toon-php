<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat\Tests;

use JimmyAhalpara\ToonFormat\ToonFormat;
use PHPUnit\Framework\TestCase;

class DecoderTest extends TestCase
{
    public function testDecodeSimpleObject(): void
    {
        $toon = "name: Alice\nage: 30";
        $result = ToonFormat::decode($toon);

        $this->assertEquals(['name' => 'Alice', 'age' => 30], $result);
    }

    public function testDecodePrimitiveArray(): void
    {
        $toon = '[3]: 1,2,3';
        $result = ToonFormat::decode($toon);

        $this->assertEquals([1, 2, 3], $result);
    }

    public function testDecodeTabularArray(): void
    {
        $toon = "[2,]{id,name}:\n  1,Alice\n  2,Bob";
        $result = ToonFormat::decode($toon);

        $expected = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testDecodeNestedObject(): void
    {
        $toon = "user:\n  name: Alice\n  age: 30";
        $result = ToonFormat::decode($toon);

        $this->assertEquals(['user' => ['name' => 'Alice', 'age' => 30]], $result);
    }

    public function testDecodePrimitives(): void
    {
        $this->assertNull(ToonFormat::decode('null'));
        $this->assertTrue(ToonFormat::decode('true'));
        $this->assertFalse(ToonFormat::decode('false'));
        $this->assertEquals(42, ToonFormat::decode('42'));
        $this->assertEquals(3.14, ToonFormat::decode('3.14'));
        $this->assertEquals('hello', ToonFormat::decode('hello'));
    }

    public function testDecodeQuotedString(): void
    {
        $this->assertEquals('hello world', ToonFormat::decode('"hello world"'));
        $this->assertEquals('null', ToonFormat::decode('"null"'));
        $this->assertEquals('123', ToonFormat::decode('"123"'));
    }

    public function testDecodeArrayWithKey(): void
    {
        $toon = 'items[2]: apple,banana';
        $result = ToonFormat::decode($toon);

        $this->assertEquals(['items' => ['apple', 'banana']], $result);
    }

    public function testDecodeEmptyArray(): void
    {
        $toon = '[0]:';
        $result = ToonFormat::decode($toon);

        $this->assertEquals([], $result);
    }

    public function testDecodeEmptyString(): void
    {
        $result = ToonFormat::decode('');
        $this->assertEquals([], $result);
    }

    public function testEncodeDecodeRoundtrip(): void
    {
        $data = [
            'name' => 'Alice',
            'age' => 30,
            'tags' => ['php', 'toon', 'coding'],
            'meta' => [
                'active' => true,
                'score' => 95.5,
            ],
        ];

        $encoded = ToonFormat::encode($data);
        $decoded = ToonFormat::decode($encoded);

        $this->assertEquals($data, $decoded);
    }

    public function testEncodeDecodeTabularRoundtrip(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Alice', 'score' => 95],
            ['id' => 2, 'name' => 'Bob', 'score' => 87],
            ['id' => 3, 'name' => 'Charlie', 'score' => 92],
        ];

        $encoded = ToonFormat::encode($data);
        $decoded = ToonFormat::decode($encoded);

        $this->assertEquals($data, $decoded);
    }
}

<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class ArrayAccessTest extends TestCase
{
    /** @var DotArray Dot Array Object */
    protected DotArray $dot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dot = new DotArray([
            [
                'first_name'    => 'Moamen',
                'last_name'     => 'Eltouny',
            ],
            [
                'first_name'    => 'Menna',
                'last_name'     => 'Elhendy',
            ]
        ]);
    }

    /**
     * Check offsetExists method on existed key
     */
    public function testOffsetExistsMethodOnExistedKey(): void
    {
        $this->assertTrue($this->dot->offsetExists('0.first_name'));
    }

    /**
     * Check offsetExists method on non existed key
     */
    public function testOffsetExistsMethodOnNonExistedKey(): void
    {
        $this->assertFalse($this->dot->offsetExists('0.non_existed_key'));
    }

    /**
     * Check offsetGet method on existed key
     */
    public function testOffsetGetMethodOnExistedKey(): void
    {
        $this->assertEquals('Moamen', $this->dot->offsetGet('0.first_name'));
    }

    /**
     * Check offsetGet method on non existed key
     */
    public function testOffsetGetMethodOnNonExistedKey(): void
    {
        $this->assertNull($this->dot->offsetGet('0.non_existed_key'));
    }

    /**
     * Check offsetSet method
     */
    public function testOffsetSetMethod(): void
    {
        $this->dot->offsetSet('0.middle_name', 'middle_name');

        $this->assertEquals('middle_name', $this->dot->offsetGet('0.middle_name'));
    }

    /**
     * Check offsetSet method on null key
     */
    public function testOffsetSetMethodOnNullKey(): void
    {
        $this->dot->offsetSet(null, 'middle_name');

        $this->assertSame('middle_name', $this->dot->get('2'));
    }

    /**
     * Check offsetUnset method
     */
    public function testOffsetUnsetMethodOnNullKey(): void
    {
        $this->dot->offsetUnset('0.first_name');

        $this->assertFalse($this->dot->offsetExists('0.first_name'));
    }

    public function testArraySyntax(): void
    {
        $dot = new DotArray();

        $dot['user.name'] = 'Raggi';
        $dot['user.email'] = null;

        $this->assertSame('Raggi', $dot['user.name']);
        $this->assertTrue(isset($dot['user.name']));
        $this->assertTrue(isset($dot['user.email']));
        $this->assertFalse(isset($dot['user.phone']));
        $this->assertNull($dot['user.phone']);

        unset($dot['user.name']);
        $this->assertSame(['user' => ['email' => null]], $dot->all());
    }

    public function testIntegerOffsets(): void
    {
        $dot = new DotArray(['a', 'b']);

        $dot[1] = 'B';
        $dot[] = 'c';

        $this->assertSame('a', $dot[0]);
        $this->assertSame(['a', 'B', 'c'], $dot->all());
        $this->assertTrue(isset($dot[2]));

        unset($dot[0]);
        $this->assertSame([1 => 'B', 2 => 'c'], $dot->all());
    }

    public function testWildcardOffsets(): void
    {
        $dot = new DotArray(['users' => [['name' => 'A'], ['name' => 'B']]]);

        $this->assertSame(['A', 'B'], $dot['users.*.name']);

        $dot['users.*.active'] = true;
        $this->assertSame([true, true], $dot['users.*.active']);

        unset($dot['users.*.name']);
        $this->assertFalse(isset($dot['users.*.name']));
    }

    public function testEscapedOffsets(): void
    {
        $dot = new DotArray(['config' => ['app.name' => 'Pharaonic']]);

        $this->assertSame('Pharaonic', $dot['config.app\.name']);
    }
}

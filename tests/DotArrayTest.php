<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use ArrayIterator;
use JsonException;
use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;
use stdClass;

class DotArrayTest extends TestCase
{
    /** @var DotArray Dot Array Object */
    protected DotArray $dot;

    /**
     * Load Dot Array
     */
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
     * Check All method
     */
    public function testAllMethod(): void
    {
        $this->assertEquals([
            [
                'first_name'    => 'Moamen',
                'last_name'     => 'Eltouny',
            ],
            [
                'first_name'    => 'Menna',
                'last_name'     => 'Elhendy',
            ]
        ], $this->dot->all());
    }

    /**
     * Check SetReference method
     */
    public function testSetReferenceMethod(): void
    {
        $items = ['Moamen', 'Eltouny', 'Pharaonic'];
        $this->dot->setReference($items);

        $items[0] = 'Menna';
        $items[1] = 'Elhendy';

        $this->assertEquals($items, $this->dot->all());
    }

    /**
     * Check Has method
     */
    public function testHasMethod(): void
    {
        $this->assertTrue($this->dot->has('*.first_name'));
    }

    /**
     * Check Get method
     */
    public function testGetMethod(): void
    {
        $this->assertEquals(['Moamen', 'Menna'], $this->dot->get('*.first_name'));
    }

    /**
     * Check Set method
     */
    public function testSetMethod(): void
    {
        $this->dot->set('1.last_name', 'Eltouny');
        $this->assertEquals('Eltouny', $this->dot->get('1.last_name'));
    }

    /**
     * Check Delete method
     */
    public function testDeleteMethod(): void
    {
        $this->dot->delete('0.last_name');

        $this->assertEmpty($this->dot->get('0.last_name'));
    }

    /**
     * Check isEmpty method on existed key
     */
    public function testIsEmptyMethodOnExistedKey(): void
    {
        $this->assertFalse($this->dot->isEmpty('0.first_name'));
    }

    /**
     * Check isEmpty method on non existed key
     */
    public function testIsEmptyMethodOnNonExistedKey(): void
    {
        $this->assertTrue($this->dot->isEmpty('0.non_existed_key'));
    }

    /**
     * Check isNumericKeys method.
     */
    public function testIsNumericKeysMethod(): void
    {
        $this->assertTrue($this->dot->isNumericKeys());
    }

    /**
     * Check isMultiDimensional method.
     */
    public function testIsMultiDimensionalMethod(): void
    {
        $this->assertTrue($this->dot->isMultiDimensional());
    }

    /**
     * Check isNulledValues method.
     */
    public function testIsNulledValuesMethod(): void
    {
        $this->assertFalse($this->dot->isNulledValues());
    }

    /**
     * Check Json method
     */
    public function testJsonMethod(): void
    {
        $json = $this->dot->toJson();

        $this->assertJson($json);
        $this->assertEquals(
            '[{"first_name":"Moamen","last_name":"Eltouny"},{"first_name":"Menna","last_name":"Elhendy"}]',
            $json
        );
    }

    /**
     * Check Clear & isEmpty method
     */
    public function testClearAndIsEmptyMethods(): void
    {
        $this->dot->clear();
        $this->assertEmpty($this->dot->all());
    }

    /**
     * Check getIterator method
     */
    public function testGetIteratorMethod(): void
    {
        $iterator = $this->dot->getIterator();

        $this->assertInstanceOf(ArrayIterator::class, $iterator);
    }

    /**
     * Check jsonSerialize method
     */
    public function testJsonSerializeMethod(): void
    {
        $this->assertEquals($this->dot->all(), $this->dot->jsonSerialize());
    }

    /**
     * Check Count method
     */
    public function testCountMethod(): void
    {
        $this->assertEquals(2, $this->dot->count('*.first_name'));
    }

    /**
     * Check setArray method on DotArray class instance
     */
    public function testSetArrayOnDotArrayInstance(): void
    {
        $dotArray = new DotArray($this->dot);

        $this->assertEquals($this->dot, $dotArray);
    }

    // Construction

    public function testConstructorAcceptsArraysObjectsScalarsAndNull(): void
    {
        $object = new stdClass();
        $object->a = 1;

        $this->assertSame([], (new DotArray())->all());
        $this->assertSame([], (new DotArray(null))->all());
        $this->assertSame(['a' => 1], (new DotArray($object))->all());
        $this->assertSame(['x'], (new DotArray('x'))->all());
    }

    public function testSetArrayReplacesItemsAndIsFluent(): void
    {
        $this->assertSame($this->dot, $this->dot->setArray(['a' => 1]));
        $this->assertSame(['a' => 1], $this->dot->all());
    }

    public function testSetArrayCopiesAnotherInstance(): void
    {
        $source = new DotArray(['a' => 1]);
        $copy = new DotArray($source);
        $copy->set('a', 2);

        $this->assertSame(['a' => 1], $source->all());
        $this->assertSame(['a' => 2], $copy->all());
    }

    // get()

    public function testGetReadsRootAndNestedValues(): void
    {
        $dot = new DotArray(['name' => 'Pharaonic', 'user' => ['profile' => ['name' => 'Raggi']]]);

        $this->assertSame('Pharaonic', $dot->get('name'));
        $this->assertSame('Raggi', $dot->get('user.profile.name'));
        $this->assertSame(['name' => 'Raggi'], $dot->get('user.profile'));
    }

    public function testGetReturnsDefaultForMissingPaths(): void
    {
        $dot = new DotArray(['user' => ['name' => 'Raggi']]);

        $this->assertNull($dot->get('user.phone'));
        $this->assertSame('x', $dot->get('user.phone', 'x'));
        $this->assertSame('x', $dot->get('missing.deep.path', 'x'));
    }

    public function testGetDoesNotDescendIntoScalars(): void
    {
        $dot = new DotArray(['user' => 'Raggi']);

        $this->assertSame('x', $dot->get('user.name', 'x'));
        $this->assertFalse($dot->has('user.name'));
    }

    public function testGetReturnsExistingFalsyValuesButDefaultForNull(): void
    {
        $dot = new DotArray(['a' => false, 'b' => 0, 'c' => '', 'd' => [], 'e' => null, 'f' => [null, null]]);

        $this->assertFalse($dot->get('a', 'x'));
        $this->assertSame(0, $dot->get('b', 'x'));
        $this->assertSame('', $dot->get('c', 'x'));
        $this->assertSame([], $dot->get('d', 'x'));
        $this->assertSame('x', $dot->get('e', 'x'));
        $this->assertNull($dot->get('e'));
        $this->assertSame([null, null], $dot->get('f', 'x'));
    }

    public function testEmptyPathReadsTheWholeArray(): void
    {
        $this->assertSame($this->dot->all(), $this->dot->get(''));
        $this->assertSame($this->dot->all(), $this->dot->get('.'));
        $this->assertTrue($this->dot->has(''));
    }

    public function testGetOnEmptyArray(): void
    {
        $dot = new DotArray([]);

        $this->assertNull($dot->get('a'));
        $this->assertSame('x', $dot->get('a.b', 'x'));
    }

    public function testGetWithDeprecatedArrayArgument(): void
    {
        $this->assertSame(1, $this->dot->get('a.b', null, ['a' => ['b' => 1]]));
        $this->assertTrue($this->dot->has('a.b', ['a' => ['b' => 1]]));
    }

    // Null semantics

    public function testNullValuesExistButGetTheDefault(): void
    {
        $dot = new DotArray(['user' => ['email' => null]]);

        $this->assertTrue($dot->has('user.email'));
        $this->assertNull($dot->get('user.email'));
        $this->assertSame('x', $dot->get('user.email', 'x'));

        $this->assertFalse($dot->has('user.phone'));
        $this->assertNull($dot->get('user.phone'));
        $this->assertSame('x', $dot->get('user.phone', 'x'));
    }

    public function testNullValuesCanBeDeleted(): void
    {
        $dot = new DotArray(['user' => ['email' => null, 'name' => 'Raggi']]);

        $this->assertTrue($dot->delete('user.email'));
        $this->assertSame(['user' => ['name' => 'Raggi']], $dot->all());
    }

    // Numeric indexes

    public function testNumericIndexes(): void
    {
        $dot = new DotArray([
            'users' => [['name' => 'A'], ['email' => 'b@example.com']],
            'matrix' => [[1, 2], [3, 4]],
        ]);

        $this->assertSame('A', $dot->get('users.0.name'));
        $this->assertSame('b@example.com', $dot->get('users.1.email'));
        $this->assertSame(2, $dot->get('matrix.0.1'));
        $this->assertSame(['name' => 'A'], $dot->get('users.0'));
    }

    public function testIntegerKeys(): void
    {
        $dot = new DotArray(['a', null, 'c']);

        $this->assertSame('a', $dot->get(0));
        $this->assertTrue($dot->has(1));
        $this->assertSame('x', $dot->get(1, 'x'));
        $this->assertFalse($dot->has(5));
        $this->assertSame('x', $dot->get(5, 'x'));

        $this->assertTrue($dot->delete(2));
        $this->assertSame(['a', null], $dot->all());
        $this->assertFalse($dot->delete(9));
    }

    public function testZeroPathIsAKeyNotAnEmptyPath(): void
    {
        $dot = new DotArray(['a', 'b']);

        $this->assertSame('a', $dot->get('0'));
        $dot->set('0', 'x');
        $this->assertSame(['x', 'b'], $dot->all());
        $this->assertTrue($dot->delete('0'));
        $this->assertSame([1 => 'b'], $dot->all());
    }

    public function testNumericStringKeysFollowPhpNormalization(): void
    {
        // PHP stores '1' as the integer key 1, so both spellings reach the same element,
        // while '01' stays a string key.
        $dot = new DotArray([0 => 'A', '1' => 'B', '01' => 'C']);

        $this->assertSame([0, 1, '01'], array_keys($dot->all()));
        $this->assertSame('B', $dot->get('1'));
        $this->assertSame('B', $dot->get(1));
        $this->assertSame('C', $dot->get('01'));

        $dot->set('2', 'D');
        $this->assertSame([0, 1, '01', 2], array_keys($dot->all()));
    }

    // set()

    public function testSetIsFluent(): void
    {
        $dot = new DotArray();

        $result = $dot
            ->set('user.name', 'Raggi')
            ->set('user.active', true);

        $this->assertSame($dot, $result);
        $this->assertSame(['user' => ['name' => 'Raggi', 'active' => true]], $dot->all());
    }

    public function testSetCreatesMissingNestedArrays(): void
    {
        $dot = new DotArray();
        $dot->set('a.b.c', 1);

        $this->assertSame(['a' => ['b' => ['c' => 1]]], $dot->all());
    }

    public function testSetReplacesExistingValues(): void
    {
        $dot = new DotArray(['a' => ['b' => 1, 'c' => 2]]);
        $dot->set('a.b', ['x' => 1]);
        $dot->set('a.c', null);

        $this->assertSame(['a' => ['b' => ['x' => 1], 'c' => null]], $dot->all());
        $this->assertTrue($dot->has('a.c'));
    }

    public function testSetReplacesNonArrayValuesInTheWay(): void
    {
        $dot = new DotArray(['a' => 'hello', 'b' => 5, 'c' => null]);
        $dot->set('a.x', 1);
        $dot->set('b.x', 2);
        $dot->set('c.x', 3);

        $this->assertSame(['a' => ['x' => 1], 'b' => ['x' => 2], 'c' => ['x' => 3]], $dot->all());
    }

    public function testSetNumericIndexes(): void
    {
        $dot = new DotArray(['users' => [['name' => 'A']]]);
        $dot->set('users.0.name', 'B');
        $dot->set('users.1.name', 'C');

        $this->assertSame(['users' => [['name' => 'B'], ['name' => 'C']]], $dot->all());
    }

    public function testSetWithEmptyPathDoesNothing(): void
    {
        $dot = new DotArray(['a' => 1]);
        $dot->set('', 'x');

        $this->assertSame(['a' => 1], $dot->all());
    }

    public function testSetWithDeprecatedArrayArgument(): void
    {
        $other = ['a' => 1];
        $this->dot->set('b.c', 2, $other);

        $this->assertSame(['a' => 1, 'b' => ['c' => 2]], $other);
        $this->assertFalse($this->dot->has('b'));
    }

    // delete()

    public function testDeleteExistingAndMissingPaths(): void
    {
        $dot = new DotArray(['a' => ['b' => ['c' => 1, 'd' => 2]], 'list' => ['x', 'y', 'z']]);

        $this->assertTrue($dot->delete('a.b.c'));
        $this->assertTrue($dot->delete('list.1'));
        $this->assertFalse($dot->delete('a.b.missing'));
        $this->assertFalse($dot->delete('missing'));

        $this->assertSame(['a' => ['b' => ['d' => 2]], 'list' => [0 => 'x', 2 => 'z']], $dot->all());
    }

    public function testDeleteWithMissingParentLeavesSiblingsAlone(): void
    {
        $dot = new DotArray(['a' => ['b' => 1], 'b' => 2]);

        $this->assertFalse($dot->delete('x.b'));
        $this->assertSame(['a' => ['b' => 1], 'b' => 2], $dot->all());
    }

    public function testDeleteDoesNotDescendIntoScalars(): void
    {
        $dot = new DotArray(['a' => 'hello']);

        $this->assertFalse($dot->delete('a.0'));
        $this->assertSame(['a' => 'hello'], $dot->all());
    }

    public function testDeleteWithEmptyPathNeverClears(): void
    {
        $dot = new DotArray(['a' => 1]);

        $this->assertFalse($dot->delete(''));
        $this->assertFalse($dot->delete('.'));
        $this->assertSame(['a' => 1], $dot->all());
    }

    public function testDeleteWithDeprecatedArrayArgument(): void
    {
        $other = ['a' => 1, 'b' => 2];

        $this->assertTrue($this->dot->delete('a', $other));
        $this->assertSame(['b' => 2], $other);
    }

    // pull()

    public function testPullReturnsAndDeletes(): void
    {
        $dot = new DotArray(['token' => 'secret', 'user' => ['password' => null]]);

        $this->assertSame('secret', $dot->pull('token'));
        $this->assertSame('x', $dot->pull('user.password', 'x'));
        $this->assertSame('x', $dot->pull('missing', 'x'));
        $this->assertSame(['user' => []], $dot->all());
    }

    // all() / clear() / isEmpty() / count()

    public function testClearIsFluent(): void
    {
        $this->assertSame($this->dot, $this->dot->clear());
        $this->assertTrue($this->dot->isEmpty());
        $this->assertCount(0, $this->dot);
    }

    public function testIsEmpty(): void
    {
        $dot = new DotArray(['a' => [], 'b' => 0, 'c' => 'x', '0' => '']);

        $this->assertFalse($dot->isEmpty());
        $this->assertTrue($dot->isEmpty('a'));
        $this->assertTrue($dot->isEmpty('b'));
        $this->assertFalse($dot->isEmpty('c'));
        $this->assertTrue($dot->isEmpty('0'));
        $this->assertTrue((new DotArray())->isEmpty());
    }

    public function testCount(): void
    {
        $dot = new DotArray(['a' => [1, 2, 3], 'b' => 'x', 'c' => null]);

        $this->assertSame(3, $dot->count());
        $this->assertCount(3, $dot);
        $this->assertSame(3, count($dot));
        $this->assertSame(3, $dot->count('a'));
        $this->assertSame(0, $dot->count('b'));
        $this->assertSame(0, $dot->count('c'));
        $this->assertSame(0, $dot->count('missing'));
    }

    // toJson()

    public function testToJsonWithKeyAndOptions(): void
    {
        $dot = new DotArray(['a' => ['b' => 'c/d'], 'list' => [[1], [2]]]);

        $this->assertSame('{"b":"c/d"}', $dot->toJson('a', JSON_UNESCAPED_SLASHES));
        $this->assertSame('[2]', $dot->toJson('list.1'));
        $this->assertSame('null', $dot->toJson('missing'));
        $this->assertSame('{"a":{"b":"c\/d"},"list":[[1],[2]]}', $dot->toJson());
    }

    public function testToJsonWithIntegerKey(): void
    {
        $dot = new DotArray([[1], [2]]);

        $this->assertSame('[1]', $dot->toJson(0));
    }

    public function testToJsonFailure(): void
    {
        $dot = new DotArray(["\xB1"]);

        $this->assertSame('', $dot->toJson());

        $this->expectException(JsonException::class);
        $dot->toJson(null, JSON_THROW_ON_ERROR);
    }

    public function testJsonEncodeUsesJsonSerialize(): void
    {
        $this->assertSame(json_encode($this->dot->all()), json_encode($this->dot));
    }

    // IteratorAggregate

    public function testIteration(): void
    {
        $dot = new DotArray(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], iterator_to_array($dot));
    }
}

<?php

namespace Pharaonic\DotArray\Test;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class DotArrayTest extends TestCase
{
    /** @var DotArray|null $dot Dot Array Object */
    protected $dot;

    /**
     * Load Dot Array
     *
     * @return void
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
    public function testAllMethod()
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
    public function testSetReferenceMethod()
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
    public function testHasMethod()
    {
        $this->assertTrue($this->dot->has('*.first_name'));
    }

    /**
     * Check Get method
     */
    public function testGetMethod()
    {
        $this->assertEquals(['Moamen', 'Menna'], $this->dot->get('*.first_name'));
    }

    /**
     * Check Set method
     */
    public function testSetMethod()
    {
        $this->dot->set('1.last_name', 'Eltouny');
        $this->assertEquals('Eltouny', $this->dot->get('1.last_name'));
    }

    /**
     * Check Delete method
     */
    public function testDeleteMethod()
    {
        $this->dot->delete('0.last_name');

        $this->assertEmpty($this->dot->get('0.last_name'));
    }

    /**
     * Check isEmpty method on existed key
     */
    public function testIsEmptyMethodOnExistedKey()
    {
        $this->assertFalse($this->dot->isEmpty('0.first_name'));
    }

    /**
     * Check isEmpty method on non existed key
     */
    public function testIsEmptyMethodOnNonExistedKey()
    {
        $this->assertTrue($this->dot->isEmpty('0.non_existed_key'));
    }

    /**
     * Check isNumericKeys method.
     */
    public function testIsNumericKeysMethod()
    {
        $this->assertTrue($this->dot->isNumericKeys());
    }

    /**
     * Check isMultiDimensional method.
     */
    public function testIsMultiDimensionalMethod()
    {
        $this->assertTrue($this->dot->isMultiDimensional());
    }

    /**
     * Check isNulledValues method.
     */
    public function testIsNulledValuesMethod()
    {
        $this->assertFalse($this->dot->isNulledValues());
    }


    /**
     * Check Json method
     */
    public function testJsonMethod()
    {
        $json = $this->dot->toJson();

        $this->assertJson($json);
        $this->assertEquals('[{"first_name":"Moamen","last_name":"Eltouny"},{"first_name":"Menna","last_name":"Elhendy"}]', $json);
    }

    /**
     * Check Clear & isEmpty method
     */
    public function testClearAndIsEmptyMethods()
    {
        $this->dot->clear();
        $this->assertEmpty($this->dot->all());
    }

    /**
     * Check offsetExists method on existed key
     */
    public function testOffsetExistsMethodOnExistedKey()
    {
        $this->assertTrue($this->dot->offsetExists('0.first_name'));
    }

    /**
     * Check offsetExists method on non existed key
     */
    public function testOffsetExistsMethodOnNonExistedKey()
    {
        $this->assertFalse($this->dot->offsetExists('0.non_existed_key'));
    }

    /**
     * Check offsetGet method on existed key
     */
    public function testOffsetGetMethodOnExistedKey()
    {
        $this->assertEquals('Moamen', $this->dot->offsetGet('0.first_name'));
    }

    /**
     * Check offsetGet method on non existed key
     */
    public function testOffsetGetMethodOnNonExistedKey()
    {
        $this->assertNull($this->dot->offsetGet('0.non_existed_key'));
    }

    /**
     * Check offsetSet method
     */
    public function testOffsetSetMethod()
    {
        $this->dot->offsetSet('0.middle_name', 'middle_name');

        $this->assertEquals('middle_name', $this->dot->offsetGet('0.middle_name'));
    }

    /**
     * Check offsetSet method on null key
     */
    public function testOffsetSetMethodOnNullKey()
    {
        $this->assertNull($this->dot->offsetSet(null, 'middle_name'));
    }

    /**
     * Check offsetUnset method
     */
    public function testOffsetUnsetMethodOnNullKey()
    {
        $this->dot->offsetUnset('0.first_name');

        $this->assertFalse($this->dot->offsetExists('0.first_name'));
    }

    /**
     * Check getIterator method
     */
    public function testGetIteratorMethod()
    {
        $iterator = $this->dot->getIterator();

        $this->assertInstanceOf(ArrayIterator::class, $iterator);
    }

    /**
     * Check jsonSerialize method
     */
    public function testJsonSerializeMethod()
    {
        $this->assertEquals($this->dot->all(), $this->dot->jsonSerialize());
    }

    /**
     * Check Count method
     */
    public function testCountMethod()
    {
        $this->assertEquals(2, $this->dot->count('*.first_name'));
    }

    /**
     * Check setArray method on DotArray class instance
     */
    public function testSetArrayOnDotArrayInstance()
    {
        $dotArray = new DotArray($this->dot);

        $this->assertEquals($this->dot, $dotArray);
    }
}

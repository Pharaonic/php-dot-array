<?php

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class HelpersTest extends TestCase
{
    /**
     * @var DotArray|null $dot Dot Array Object
     */
    protected $dot;

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
     * Check Dot
     */
    public function testDotFunction()
    {
        $this->assertInstanceOf(DotArray::class, dot());
    }

    /**
     * Check array_is_numeric
     */
    public function testIsNumericArrayFunction()
    {
        $this->assertTrue(array_is_numeric($this->dot->all()));
    }

    /**
     * Check array_is_null
     */
    public function testIsNullArrayFunction()
    {
        $this->dot->clear();
        $this->assertTrue(array_is_null($this->dot->all()));
    }

    /**
     * Check array_is_null
     */
    public function testIsMultiDimensionalArrayFunction()
    {
        $this->assertTrue(array_is_multidimensional($this->dot->all()));
    }
}

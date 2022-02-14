<?php

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class HelpersTest extends TestCase
{
    /** @var DotArray|null $dot Dot Array Object */
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
     * Check is_numeric_array
     */
    public function testIsNumericArrayFunction()
    {
        $this->assertTrue(is_numeric_array($this->dot->all()));
    }

    /**
     * Check is_null_array
     */
    public function testIsNullArrayFunction()
    {
        $this->dot->clear();
        $this->assertTrue(is_null_array($this->dot->all()));
    }

    /**
     * Check is_null_array
     */
    public function testIsMultiDimensionalArrayFunction()
    {
        $this->assertTrue(is_multidimensional_array($this->dot->all()));
    }
}

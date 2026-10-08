<?php

declare(strict_types=1);

namespace Pharaonic\DotArray\Test;

use PHPUnit\Framework\TestCase;
use Pharaonic\DotArray\DotArray;

class HelpersTest extends TestCase
{
    /**
     * @var DotArray Dot Array Object
     */
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
     * Check Dot
     */
    public function testDotFunction(): void
    {
        $this->assertInstanceOf(DotArray::class, dot());
    }

    public function testDotFunctionArguments(): void
    {
        $this->assertSame(['a' => 1], dot(['a' => 1])->all());
        $this->assertSame([], dot(null)->all());
        $this->assertSame(['a' => 1], dot(dot(['a' => 1]))->all());
    }

    /**
     * Check array_is_numeric
     */
    public function testIsNumericArrayFunction(): void
    {
        $this->assertTrue(array_is_numeric($this->dot->all()));
        $this->assertFalse(array_is_numeric(['a' => 1]));
        $this->assertFalse(array_is_numeric([1 => 'a']));
        $this->assertTrue(array_is_numeric([]));
    }

    /**
     * Check array_is_null
     */
    public function testIsNullArrayFunction(): void
    {
        $this->dot->clear();
        $this->assertTrue(array_is_null($this->dot->all()));
        $this->assertTrue(array_is_null([null, null]));
        $this->assertFalse(array_is_null([null, 0]));
    }

    /**
     * Check array_is_multidimensional
     */
    public function testIsMultiDimensionalArrayFunction(): void
    {
        $this->assertTrue(array_is_multidimensional($this->dot->all()));
        $this->assertFalse(array_is_multidimensional([1, 2]));
        $this->assertTrue(array_is_multidimensional(['a' => []]));
    }

    public function testDeprecatedInspectionMethods(): void
    {
        $dot = new DotArray(['a' => null]);

        $this->assertFalse($dot->isNumericKeys());
        $this->assertFalse($dot->isMultidimensional());
        $this->assertTrue($dot->isNulledValues());

        $empty = new DotArray(['a' => []]);
        $this->assertTrue($empty->isMultidimensional());
        $this->assertTrue(new DotArray()->isNumericKeys());
    }
}

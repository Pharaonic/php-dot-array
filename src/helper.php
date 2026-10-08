<?php

declare(strict_types=1);

use Pharaonic\DotArray\DotArray;
use Pharaonic\Readable\Arr;

if (!function_exists('dot')) {
    /**
     * Create new Dot-Array object
     *
     * @param mixed $items An array, another DotArray, or null for an empty one.
     */
    function dot(mixed $items = []): DotArray
    {
        return new DotArray($items);
    }
}

if (!function_exists('array_is_numeric')) {
    /**
     * Check if the given array's keys are integers from 0 to N, in order
     *
     * @deprecated Use Pharaonic\Readable\Arr::isList(); will be removed in a future major release.
     * @param array<mixed> $arr
     */
    function array_is_numeric(array $arr): bool
    {
        return Arr::isList($arr);
    }
}

if (!function_exists('array_is_null')) {
    /**
     * Check if the given array contains Null values only
     *
     * @deprecated Use Pharaonic\Readable\Arr::isNull(); will be removed in a future major release.
     * @param array<mixed> $arr
     */
    function array_is_null(array $arr): bool
    {
        return Arr::isNull($arr);
    }
}

if (!function_exists('array_is_multidimensional')) {
    /**
     * Check if any value of the given array is an array
     *
     * @deprecated Use Pharaonic\Readable\Arr::isMultidimensional(); will be removed in a future major release.
     * @param array<mixed> $arr
     */
    function array_is_multidimensional(array $arr): bool
    {
        return Arr::isMultidimensional($arr);
    }
}

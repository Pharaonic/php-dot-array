<?php

use Pharaonic\DotArray\DotArray;

/**
 * Create new Dot-Array object
 * 
 * @param array|null $arr Original Array
 * @return Raggitech\DotArray\DotArray
 */
function dot(array $arr = NULL)
{
    return new DotArray($arr);
}

/**
 * Check if the given array is a numeric array
 * 
 * @param array $arr array
 * @return bool
 */
function array_is_numeric(array $arr)
{
    return array_keys($arr) === range(0, count($arr) - 1);
}

/**
 * Check if the given array contains Null values only
 * 
 * @param array $arr array
 * @return bool
 */
function array_is_null(array $arr)
{
    return empty(array_filter($arr, function ($v) {
        return $v !== null;
    }));
}

/**
 * Check if the given array is a multidimensional
 * 
 * @param array $arr array
 * @return bool
 */
function array_is_multidimensional(array $arr)
{
    return count($arr) !== count($arr, COUNT_RECURSIVE);
}

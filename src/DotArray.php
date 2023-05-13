<?php

namespace Pharaonic\DotArray;

use Countable, ArrayAccess, ArrayIterator, JsonSerializable, IteratorAggregate;
use Traversable;

/**
 * Dot Array Class
 *
 * Access array data quickly/easily using dot-notation.
 *
 * @package Pharaonic\DotArray
 * @author Moamen Eltouny <raggigroup@gmail.com>
 * @version 0.0.2
 * @see https://github.com/pharaonic/php-dot-array
 */
class DotArray implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Array Items
     *
     * @var array
     */
    protected $_ITEMS = [];

    /**
     * Create an new DoArray instance
     *
     * @param mixed $items
     */
    public function __construct(mixed $items = [])
    {
        $this->setArray($items);
    }

    /**
     * Set Array Items
     *
     * @param   mixed $items
     * @return  void
     **/
    public function setArray($items)
    {
        if ($items instanceof static) {
            $this->_ITEMS = $items->all();
        } elseif (is_array($items)) {
            $this->_ITEMS = $items;
        } else {
            $this->_ITEMS = (array) $items;
        }
    }

    /**
     * Set Reference Array
     *
     * @param   array $items
     * @return  void
     **/
    public function setReference(array &$items)
    {
        $this->_ITEMS = &$items;
    }

    /**
     * Get all the stored items
     *
     * @return array
     */
    public function all()
    {
        return $this->_ITEMS;
    }

    /**
     * Clear all stored items
     *
     * @return void
     */
    public function clear()
    {
        $this->_ITEMS = [];
    }

    /**
     * Check if a given key exists
     *
     * @param   string|int $keys
     * @return  bool
     */
    public function has(string|int $key, array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;

        if (is_int($key) && isset($items[$key])) {
            return true;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            for ($index = 0; $index < count($key); $index++) {
                if (is_array($items)) {
                    if ($key[$index] == '*') {
                        $index++;
                        $next_key = implode('.', array_slice($key, $index));

                        foreach ($items as $item)
                            if (!$this->has($next_key, $item)) return false;

                        break;
                    } else {
                        if (!array_key_exists($key[$index], $items)) return false;
                        $items = $items[$key[$index]];
                    }
                } else {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Return the value of a given key
     *
     * @param   string|int $key
     * @param   array|string|int|float|null $default
     * @return  mixed
     */
    public function get(string|int $key, mixed $default = null, array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;

        if (is_int($key)) {
            return isset($items[$key]) ? $items[$key] : $default;
        } else {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index < count($key); $index++) {
                if ($key[$index] == '*') {
                    if (!is_array($items)) {
                        return $default;
                    }

                    $index++;
                    $next_key = implode('.', array_slice($key, $index));
                    $rs = null;

                    foreach ($items as $k => $item) {
                        $item = $this->get($next_key, $default, $item);
                        $rs[] = $item;
                    }

                    $items = $rs;
                    break;
                } else {
                    $items = is_array($items) && array_key_exists($key[$index], $items) ? $items[$key[$index]] : null;
                }
            }

            // if multidimensional
            if (is_array($items) && array_is_multidimensional($items) && array_is_numeric($items) && $index = $max) {
                if (isset($items[0][0]) && \is_array($items[0][0]))
                    foreach ($items as &$item) {
                        $item = array_merge_recursive(...$item);
                    }

                $items = array_merge_recursive(...$items);
            }

            if (is_array($items) && array_is_null($items)) {
                $items = null;
            }

            return is_null($items) ? $default : $items;
        }
    }

    /**
     * Set a given value to the given key
     *
     * @param   string                      $key
     * @param   array|int|float|string|null $value
     * @return  void
     */
    public function set(string $key, mixed $value = null, array &$arr = null)
    {
        if (!$arr) {
            $items = &$this->_ITEMS;
        } else {
            $items = &$arr;
        }

        if (is_int($key)) {
            $items[$key] = $value;
            return;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index <= $max; $index++) {
                if ($index == $max) {
                    $items[$key[$index]] = $value;
                } else {
                    if ($key[$index] == '*') {
                        $index++;
                        $next_key = implode('.', array_slice($key, $index));

                        if (empty($items)) {
                            $items[][$key[$index]] = null;
                        }

                        foreach ($items as &$item) {
                            $this->set($next_key, $value, $item);
                        }

                        break;
                    } else {
                        if (!isset($items[$key[$index]])) {
                            $items[$key[$index]] = null;
                        }

                        $items = &$items[$key[$index]];
                    }
                }
            }
        }
    }

    /**
     * Delete the given key
     *
     * @param   string|int $key
     * @return  bool
     */
    public function delete(string|int $key, array &$arr = null): bool
    {
        if (!$arr) {
            $items = &$this->_ITEMS;
        } else {
            $items = &$arr;
        }

        if (is_int($key) && isset($items[$key])) {
            unset($items[$key[$index]]);
            return true;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index <= $max; $index++) {
                if ($index == $max) {
                    if (isset($items[$key[$index]])) {
                        unset($items[$key[$index]]);
                        return true;
                    }
                } else {
                    if ($key[$index] == '*') {
                        $index++;
                        $next_key = implode('.', array_slice($key, $index));
                        $rs = true;

                        foreach ($items as &$item) {
                            if (!$this->delete($next_key, $item)) {
                                $rs = false;
                            }
                        }

                        return $rs;
                    } elseif (isset($items[$key[$index]])) {
                        $items = &$items[$key[$index]];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if the given key's value is empty
     *
     * @param   string|null $key
     * @return  bool
     */
    public function isEmpty(string $key = null): bool
    {
        if (!$key) {
            return empty($this->_ITEMS);
        }

        return empty($this->get($key ?? '*'));
    }

    /**
     * Check if the given keys are integers from 0 to N
     *
     * @return boolean
     */
    public function isNumericKeys(): bool
    {
        return array_is_numeric($this->_ITEMS);
    }

    /**
     * Check if the array is a multidimensional
     *
     * @return boolean
     */
    public function isMultidimensional(): bool
    {
        return array_is_multidimensional($this->_ITEMS);
    }

    /**
     * Check if the array contains Null values only
     *
     * @return boolean
     */
    public function isNulledValues(): bool
    {
        return array_is_null($this->_ITEMS);
    }

    /**
     * Return the value of a given key as JSON
     *
     * @param   int|string|null $key
     * @param   int         $options
     * @return  string
     */
    public function toJson(int|string $key = null, int $options = 0): string
    {
        return json_encode($key ? $this->get($key ?? '*') : $this->all(), $options);
    }

    /**
     * Prepare Key to Array of Keys
     * 
     * @param  string $key
     * @return array
     */
    private function prepareKey(string $key): array
    {
        $key = rtrim(
            trim($key, '. '),
            '.*'
        );

        return empty($key) ? [] : explode('.', $key);
    }

    /**
     * Check if a given key exists
     *
     * @param  int|string $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Return the value of a given key
     *
     * @param  int|string $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given key
     *
     * @param  int|string $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->_ITEMS[] = $value;
            return;
        }

        $this->set($key, $value);
    }

    /**
     * Delete the given key
     * 
     * @param  int|string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    /**
     * Return the number of items in a given key
     *
     * @param  string|null $key
     * @return  int
     */
    public function count($key = null): int
    {
        return count($this->get($key ?? '*'));
    }

    /**
     * Get an iterator for the stored items
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->_ITEMS);
    }

    /**
     * Return items for JSON serialization
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->_ITEMS;
    }
};

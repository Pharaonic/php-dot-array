<?php

namespace Pharaonic\DotArray;

use Countable, ArrayAccess, ArrayIterator, JsonSerializable, IteratorAggregate;

/**
 * Dot Array Class
 *
 * Access array data quickly/easily using dot-notation.
 *
 * @package Pharaonic\DotArray
 * @author Moamen Eltouny <support@pharaonic.com>
 * @version 0.0.2
 * @see https://github.com/pharaonic/php-dot-array
 */
class DotArray implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /** @var array $_ITEMS Original source [Array] */
    protected $_ITEMS = [];

    /**
     * Create an new DoArray instance
     *
     * @param mixed $items
     */
    public function __construct($items = [])
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
        if ($items instanceof self) {
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
     * @param   string $keys
     * @return  bool
     */
    public function has(string $key, array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;
        $max = count($items) - 1;
        $this->prepareKey($key);
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

    /**
     * Return the value of a given key
     *
     * @param   string $key
     * @param   string $default
     * @return  mixed
     */
    public function get(string $key, $default = null, array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;
        $this->prepareKey($key);
        $max = count($key) - 1;

        for ($index = 0; $index < count($key); $index++) {
            if ($key[$index] == '*') {
                if (!is_array($items)) return $default;

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
        if (is_array($items) && is_multidimensional_array($items) && is_numeric_array($items) && $index = $max) {
            if (isset($items[0][0]) && \is_array($items[0][0]))
                foreach ($items as &$item)
                    $item = array_merge_recursive(...$item);

            $items = array_merge_recursive(...$items);
        }

        if (is_array($items) && is_null_array($items)) $items = null;

        return is_null($items) ? $default : $items;
    }

    /**
     * Set a given value to the given key
     *
     * @param   string                      $key
     * @param   array|int|float|string|null $value
     * @return  void
     */
    public function set(string $key, $value, array &$arr = null)
    {
        $items = &$arr;
        if (!$arr) $items = &$this->_ITEMS;

        $this->prepareKey($key);
        $max = count($key) - 1;

        for ($index = 0; $index <= $max; $index++) {
            if ($index == $max) {
                $items[$key[$index]] = $value;
            } else {
                if ($key[$index] == '*') {
                    $index++;
                    $next_key = implode('.', array_slice($key, $index));

                    if (empty($items)) $items[][$key[$index]] = null;

                    foreach ($items as &$item)
                        $this->set($next_key, $value, $item);

                    break;
                } else {
                    if (!isset($items[$key[$index]])) $items[$key[$index]] = null;
                    $items = &$items[$key[$index]];
                }
            }
        }
    }

    /**
     * Delete the given key
     *
     * @param   string $key
     * @return  bool
     */
    public function delete(string $key, array &$arr = null): bool
    {

        $items = &$arr;
        if (!$arr) $items = &$this->_ITEMS;

        $this->prepareKey($key);
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

                    foreach ($items as &$item)
                        if (!$this->delete($next_key, $item)) $rs = false;

                    return $rs;
                } else {
                    if (isset($items[$key[$index]])) $items = &$items[$key[$index]];
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
        return empty($this->get($key ?? '*'));
    }

    /**
     * Return the value of a given key as JSON
     *
     * @param   string|null $key
     * @param   int         $options
     * @return  string
     */
    public function toJson(string $key = null, $options = 0): string
    {
        return json_encode($key ? $this->get($key ?? '*') : $this->all(), $options);
    }

    /**
     * Prepare Key to Array of Keys
     */
    private function prepareKey(string &$key)
    {
        $key = trim($key, '. ');
        $key = rtrim($key, '.*');
        $key = empty($key) ? [] : explode('.', $key);
    }



    # ArrayAccess interface

    /**
     * Check if a given key exists
     *
     * @param  int|string $key
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Return the value of a given key
     *
     * @param  int|string $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given key
     *
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->_ITEMS[] = $value;
            return;
        }
        $this->set($key, $value);
    }

    /**
     * Delete the given key
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->delete($key);
    }



    # Countable interface

    /**
     * Return the number of items in a given key
     *
     * @return  int
     */
    public function count($key = null): int
    {
        return count($this->get($key ?? '*'));
    }



    # IteratorAggregate interface

    /**
     * Get an iterator for the stored items
     *
     * @return ArrayIterator
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->_ITEMS);
    }



    # JsonSerializable interface

    /**
     * Return items for JSON serialization
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->_ITEMS;
    }
};

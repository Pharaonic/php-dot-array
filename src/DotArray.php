<?php

declare(strict_types=1);

namespace Pharaonic\DotArray;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Pharaonic\Readable\Arr;
use Traversable;

/**
 * Dot Array Class
 *
 * Read, write, check and delete values in deeply nested arrays using
 * dot-notation paths and `*` wildcards.
 *
 * Paths:
 * - `.` separates keys: `user.profile.name`.
 * - A segment that is exactly `*` matches every element of the array it is applied to.
 * - `\.`, `\*` and `\\` are a literal dot, star and backslash inside a key.
 *   Any other backslash is kept as is (`App\Models` is one key).
 * - Leading and trailing dots and spaces are ignored; an empty path means the whole array.
 *
 * Wildcards: every operation first finds the elements reached by the last `*`
 * (skipping branches that don't reach it), then applies the rest of the path to each of them.
 *
 * @package Pharaonic\DotArray
 * @author Moamen Eltouny <raggigroup@gmail.com>
 * @see https://github.com/pharaonic/php-dot-array
 *
 * @implements ArrayAccess<int|string, mixed>
 * @implements IteratorAggregate<array-key, mixed>
 */
class DotArray implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Array Items
     *
     * @var array<mixed>
     */
    protected $_ITEMS = [];

    /**
     * Create a new DotArray instance
     *
     * @param mixed $items An array, another DotArray, or any value castable to an array.
     */
    public function __construct(mixed $items = [])
    {
        $this->setArray($items);
    }

    /**
     * Replace the stored items
     *
     * When a reference is set, the referenced array is replaced.
     *
     * @param   mixed $items An array, another DotArray, or any value castable to an array.
     * @return  $this
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

        return $this;
    }

    /**
     * Work on the given array by reference
     *
     * @param   array<mixed> $items
     * @return  $this
     **/
    public function setReference(array &$items)
    {
        $this->_ITEMS = &$items;

        return $this;
    }

    /**
     * Get all the stored items
     *
     * @return array<mixed>
     */
    public function all()
    {
        return $this->_ITEMS;
    }

    /**
     * Clear all stored items
     *
     * @return $this
     */
    public function clear()
    {
        $this->_ITEMS = [];

        return $this;
    }

    /**
     * Check if a given path exists, even when its value is null
     *
     * With wildcards, true when at least one element is matched and every matched element has the path.
     *
     * @param   string|int $key
     * @param   array<mixed>|null $arr Deprecated: an array to check instead of the stored items.
     * @return  bool
     */
    public function has(string|int $key, ?array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;
        $parts = $this->parsePath($key);

        if (count($parts) === 1) {
            return $this->lookup($items, $parts[0])[0];
        }

        [$matches, $tail] = $this->resolve($items, $parts);

        foreach ($matches as $prefix) {
            if (!$this->lookup($items, array_merge($prefix, $tail))[0]) {
                return false;
            }
        }

        return $matches !== [];
    }

    /**
     * Return the value of a given path, or the default when it is missing or null
     *
     * With wildcards, returns a list with one entry per matched element (the default where
     * that element lacks the path or holds null), or the default when no element is matched.
     *
     * @param   string|int $key
     * @param   mixed $default
     * @param   array<mixed>|null $arr Deprecated: an array to read instead of the stored items.
     * @return  mixed
     */
    public function get(string|int $key, mixed $default = null, ?array $arr = null)
    {
        $items = $arr ?? $this->_ITEMS;
        $parts = $this->parsePath($key);

        if (count($parts) === 1) {
            return $this->lookup($items, $parts[0])[1] ?? $default;
        }

        [$matches, $tail] = $this->resolve($items, $parts);

        if ($matches === []) {
            return $default;
        }

        $values = [];

        foreach ($matches as $prefix) {
            $values[] = $this->lookup($items, array_merge($prefix, $tail))[1] ?? $default;
        }

        return $values;
    }

    /**
     * Set a given value to the given path
     *
     * Missing keys are created, and a non-array value in the way is replaced by an array.
     * Wildcards only update existing elements; they never create new ones.
     *
     * @param   string $key
     * @param   mixed $value
     * @param   array<mixed>|null $arr Deprecated: an array to modify instead of the stored items.
     * @return  $this
     */
    public function set(string $key, mixed $value = null, ?array &$arr = null)
    {
        if ($arr === null) {
            $items = &$this->_ITEMS;
        } else {
            $items = &$arr;
        }

        $parts = $this->parsePath($key);

        if ($parts === [[]]) {
            return $this;
        }

        [$matches, $tail] = $this->resolve($items, $parts);

        foreach ($matches as $prefix) {
            $node = &$this->locate($items, $prefix);

            foreach ($tail as $segment) {
                if (!is_array($node)) {
                    $node = [];
                }

                $node = &$node[$segment];
            }

            $node = $value;
            unset($node);
        }

        return $this;
    }

    /**
     * Delete the given path
     *
     * @param   string|int $key
     * @param   array<mixed>|null $arr Deprecated: an array to modify instead of the stored items.
     * @return  bool True when at least one value was deleted.
     */
    public function delete(string|int $key, ?array &$arr = null): bool
    {
        if ($arr === null) {
            $items = &$this->_ITEMS;
        } else {
            $items = &$arr;
        }

        $parts = $this->parsePath($key);

        if ($parts === [[]]) {
            return false;
        }

        [$matches, $tail] = $this->resolve($items, $parts);
        $deleted = false;

        foreach ($matches as $prefix) {
            $keys = array_merge($prefix, $tail);
            $last = array_pop($keys);
            $node = &$items;

            foreach ($keys as $segment) {
                if (!is_array($node) || !array_key_exists($segment, $node)) {
                    continue 2;
                }

                $node = &$node[$segment];
            }

            if ($last !== null && is_array($node) && array_key_exists($last, $node)) {
                unset($node[$last]);
                $deleted = true;
            }
        }

        unset($node);

        return $deleted;
    }

    /**
     * Return the value of a given path and delete it
     *
     * @param   string|int $key
     * @param   mixed $default
     * @return  mixed
     */
    public function pull(string|int $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->delete($key);

        return $value;
    }

    /**
     * Check if the given path's value is empty
     *
     * @param   string|null $key Without a path, checks the whole array.
     */
    public function isEmpty(?string $key = null): bool
    {
        if ($key === null || $key === '') {
            return $this->_ITEMS === [];
        }

        return empty($this->get($key));
    }

    /**
     * Check if the keys are integers from 0 to N, in order
     *
     * @deprecated Use Pharaonic\Readable\Arr::isList($dot->all()); will be removed in a future major release.
     */
    public function isNumericKeys(): bool
    {
        return Arr::isList($this->_ITEMS);
    }

    /**
     * Check if any value is an array
     *
     * @deprecated Use Pharaonic\Readable\Arr::isMultidimensional($dot->all());
     *             Will be removed in a future major release.
     */
    public function isMultidimensional(): bool
    {
        return Arr::isMultidimensional($this->_ITEMS);
    }

    /**
     * Check if the array contains Null values only
     *
     * @deprecated Use Pharaonic\Readable\Arr::isNull($dot->all()); will be removed in a future major release.
     */
    public function isNulledValues(): bool
    {
        return Arr::isNull($this->_ITEMS);
    }

    /**
     * Return the whole array, or the value of a given path, as JSON
     *
     * Returns an empty string when encoding fails, unless JSON_THROW_ON_ERROR is passed.
     *
     * @param   int|string|null $key
     * @param   int $options json_encode() flags
     */
    public function toJson(int|string|null $key = null, int $options = 0): string
    {
        $json = json_encode($key === null ? $this->_ITEMS : $this->get($key), $options);

        return $json === false ? '' : $json;
    }

    /**
     * Split a path into the key lists found between its wildcards
     *
     * `groups.*.users.*.name` becomes [['groups'], ['users'], ['name']]: one wildcard sits
     * between every two lists, so a path without wildcards gives a single list.
     *
     * @return non-empty-list<list<int|string>>
     */
    private function parsePath(string|int $path): array
    {
        if (is_int($path)) {
            return [[$path]];
        }

        $path = $this->trimPath($path);

        if ($path === '') {
            return [[]];
        }

        $parts = [[]];
        $part = 0;
        $segment = '';
        $escaped = false;
        $length = strlen($path);

        for ($i = 0; $i <= $length; $i++) {
            $char = $i < $length ? $path[$i] : '.';

            if ($char === '\\' && $i + 1 < $length && strpos('.*\\', $path[$i + 1]) !== false) {
                $segment .= $path[++$i];
                $escaped = true;
            } elseif ($char === '.') {
                if ($segment === '*' && !$escaped) {
                    $parts[++$part] = [];
                } else {
                    $parts[$part][] = $segment;
                }

                $segment = '';
                $escaped = false;
            } else {
                $segment .= $char;
            }
        }

        return $parts;
    }

    /**
     * Strip leading and trailing dots and spaces, keeping an escaped trailing dot
     */
    private function trimPath(string $path): string
    {
        $path = ltrim($path, '. ');
        $end = strlen($path);

        while ($end > 0 && ($path[$end - 1] === '.' || $path[$end - 1] === ' ')) {
            if ($path[$end - 1] === '.') {
                $backslashes = strspn(strrev(substr($path, 0, $end - 1)), '\\');

                if ($backslashes % 2 === 1) {
                    break;
                }
            }

            $end--;
        }

        return substr($path, 0, $end);
    }

    /**
     * Find the elements reached by a path's last wildcard
     *
     * Returns the concrete key list leading to every matched element, and the keys that
     * follow the last wildcard. Branches missing a key or holding a non-array before the
     * last wildcard are skipped, and so are non-array elements when keys follow it.
     * A path without wildcards gives a single empty key list.
     *
     * @param  array<mixed> $items
     * @param  non-empty-list<list<int|string>> $parts
     * @return array{list<list<int|string>>, list<int|string>}
     */
    private function resolve(array $items, array $parts): array
    {
        $tail = $parts[count($parts) - 1];

        if (count($parts) === 1) {
            return [[[]], $tail];
        }

        $matches = [];
        $this->collect($items, $parts, 0, [], $matches);

        return [$matches, $tail];
    }

    /**
     * Follow one key list and the wildcard after it, recursing into the next list
     *
     * @param  non-empty-list<list<int|string>> $parts
     * @param  list<int|string> $prefix
     * @param  list<list<int|string>> $matches
     */
    private function collect(mixed $node, array $parts, int $part, array $prefix, array &$matches): void
    {
        foreach ($parts[$part] as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return;
            }

            $node = $node[$segment];
            $prefix[] = $segment;
        }

        if (!is_array($node)) {
            return;
        }

        $next = $part + 1;
        $isLast = $next === count($parts) - 1;

        foreach ($node as $key => $child) {
            $keys = $prefix;
            $keys[] = $key;

            if (!$isLast) {
                $this->collect($child, $parts, $next, $keys, $matches);
            } elseif ($parts[$next] === [] || is_array($child)) {
                $matches[] = $keys;
            }
        }
    }

    /**
     * Read the value under a key list
     *
     * @param  list<int|string> $keys
     * @return array{bool, mixed} Whether it exists, and its value.
     */
    private function lookup(mixed $node, array $keys): array
    {
        foreach ($keys as $key) {
            if (!is_array($node) || !array_key_exists($key, $node)) {
                return [false, null];
            }

            $node = $node[$key];
        }

        return [true, $node];
    }

    /**
     * Get a reference to the element under a key list returned by resolve()
     *
     * @param  array<mixed> $items
     * @param  list<int|string> $keys Keys known to exist.
     */
    private function &locate(array &$items, array $keys): mixed
    {
        $node = &$items;

        foreach ($keys as $key) {
            /** @var array<mixed> $node */
            $node = &$node[$key];
        }

        return $node;
    }

    /**
     * Check if a given path exists
     *
     * @param  int|string $key
     */
    #[\Override]
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Return the value of a given path
     *
     * @param  int|string $key
     */
    #[\Override]
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given path, or append it when the path is null
     *
     * @param  int|string|null $key
     * @param  mixed $value
     */
    #[\Override]
    public function offsetSet($key, $value): void
    {
        if ($key === null) {
            $this->_ITEMS[] = $value;
            return;
        }

        $this->set((string) $key, $value);
    }

    /**
     * Delete the given path
     *
     * @param  int|string $key
     */
    #[\Override]
    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    /**
     * Return the number of items in the whole array, or in a given path
     *
     * A missing path or a non-array value counts as 0.
     *
     * @param  int|string|null $key
     */
    #[\Override]
    public function count($key = null): int
    {
        $value = $key === null ? $this->_ITEMS : $this->get($key);

        return is_array($value) ? count($value) : 0;
    }

    /**
     * Get an iterator for the stored items
     *
     * @return ArrayIterator<array-key, mixed>
     */
    #[\Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->_ITEMS);
    }

    /**
     * Return items for JSON serialization
     */
    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->_ITEMS;
    }
}

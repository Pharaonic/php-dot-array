## API Reference

### `Pharaonic\DotArray\DotArray`

| Method | Description | Returns |
| --- | --- | --- |
| `__construct(mixed $items = [])` | Create an instance from an array, another `DotArray`, or any value castable to an array. | |
| `get(string\|int $key, mixed $default = null)` | The value at a path, or `$default` when it doesn't exist. A list with a wildcard. | `mixed` |
| `set(string $key, mixed $value = null)` | Set a value, creating missing keys. | `$this` |
| `has(string\|int $key)` | Whether the path exists, even when its value is `null`. | `bool` |
| `delete(string\|int $key)` | Delete a path. | `bool` (`true` when something was deleted) |
| `pull(string\|int $key, mixed $default = null)` | Return the value like `get()`, then delete it. | `mixed` |
| `all()` | The whole array. | `array` |
| `clear()` | Empty the array. | `$this` |
| `setArray(mixed $items)` | Replace the array. | `$this` |
| `setReference(array &$items)` | Work on the given array by reference. | `$this` |
| `count(string\|int\|null $key = null)` | The number of top-level items, or of items at a path (`0` when missing or not an array). | `int` |
| `isEmpty(?string $key = null)` | Whether the array, or the value at a path, is empty. | `bool` |
| `toJson(int\|string\|null $key = null, int $options = 0)` | The array, or the value at a path, as JSON (`''` on failure). | `string` |
| `getIterator()` | Iterator over the top-level items. | `ArrayIterator` |
| `jsonSerialize()` | The array, for `json_encode()`. | `array` |
| `offsetGet()` / `offsetSet()` / `offsetExists()` / `offsetUnset()` | `ArrayAccess`: see [ArrayAccess & Interfaces](#array-access). | |

### Helper

| Function | Description | Returns |
| --- | --- | --- |
| `dot(mixed $items = [])` | Same as `new DotArray($items)`. | `DotArray` |

### Deprecated

These still work, but will be removed in a future major release:

| Name | Use instead |
| --- | --- |
| `isNumericKeys()`, `array_is_numeric(array $arr)` | `Pharaonic\Readable\Arr::isList($array)` |
| `isMultidimensional()`, `array_is_multidimensional(array $arr)` | `Pharaonic\Readable\Arr::isMultidimensional($array)` |
| `isNulledValues()`, `array_is_null(array $arr)` | `Pharaonic\Readable\Arr::isNull($array)` |
| The third argument of `get()`, `has()`, `set()` and `delete()` (an array to work on) | `dot($array)` or `setReference($array)` |

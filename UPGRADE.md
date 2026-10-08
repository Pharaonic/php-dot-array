# Upgrade Guide

## From 2.x to 8.0

Version 8.0 rebuilds the path engine for PHP 8.0. The public API is unchanged: every method and helper still exists with the same parameters, and `delete()` still returns `bool`. What changed is the result of some calls, mostly edge cases that were bugs, but some of them could be relied on.

### Requirements

- PHP `>=8.0 <8.1` (one release line per PHP version: `8.0.x`, `8.1.x`, ...).
- `pharaonic/php-readable` `~8.0.1` (installed automatically), which requires `ext-mbstring`.

### API mapping

| Before                                              | Now                                         | Status                                    |
|-----------------------------------------------------|---------------------------------------------|-------------------------------------------|
| `get()`, `has()`, `set()`, `delete()`               | unchanged                                   | Supported                                 |
| `all()`, `clear()`, `count()`, `isEmpty()`, `toJson()` | unchanged                                | Supported                                 |
| `setArray()`, `setReference()`, ArrayAccess, `dot()` | unchanged                                  | Supported                                 |
| `set()`, `clear()`, `setArray()`, `setReference()` returned nothing | return the instance (chainable) | Supported                                 |
| `get()` then `delete()`                             | `pull($path, $default)`                     | New                                       |
| `isNumericKeys()`, `array_is_numeric()`             | `Pharaonic\Readable\Arr::isList()`          | Deprecated (works, no runtime warning)    |
| `isMultidimensional()`, `array_is_multidimensional()` | `Pharaonic\Readable\Arr::isMultidimensional()` | Deprecated (works, no runtime warning) |
| `isNulledValues()`, `array_is_null()`               | `Pharaonic\Readable\Arr::isNull()`          | Deprecated (works, no runtime warning)    |
| third argument of `get()`, `has()`, `set()`, `delete()` | `dot($array)` or `setReference($array)` | Deprecated (works, no runtime warning)    |

Subclasses of `DotArray` keep working: no parameter or declared return type changed.

### Behavior changes

#### 1. `[]` and all-`null` arrays are returned, and `null` values exist

`get()` still gives the default for a missing path or a `null` value, as before. It no longer treats `[]` or an array holding only `null`s as missing, and the other methods now see `null` values.

| Data                     | Call                 | Before | Now            |
|--------------------------|----------------------|--------|----------------|
| `['a' => null]`          | `get('a', 'x')`      | `'x'`  | `'x'` (unchanged) |
| `['a' => []]`            | `get('a', 'x')`      | `'x'`  | `[]`           |
| `['a' => [null, null]]`  | `get('a', 'x')`      | `'x'`  | `[null, null]` |
| `['a' => null]`          | `delete('a')`        | `false`, kept | `true`, deleted |
| `[null]`                 | `has(0)`             | `null` | `true`         |

If you need the old fallback for empty arrays, apply it yourself:

```php
$dot->get('a') ?: 'x';  // default for anything empty
```

#### 2. Wildcard reads return one entry per element

Results used to be merged with `array_merge_recursive()` whenever the values were arrays. Now every matched element gives exactly one entry, in order. Scalar results and nested wildcards are unchanged.

| Data                                                  | Call                    | Before            | Now                           |
|-------------------------------------------------------|-------------------------|-------------------|-------------------------------|
| `['users' => [['p' => ['e' => 1]], ['p' => ['e' => 2]]]]` | `get('users.*.p')`  | `['e' => [1, 2]]` | `[['e' => 1], ['e' => 2]]`    |
| `['a' => [['tags' => [1, 2]], ['tags' => [3]]]]`      | `get('a.*.tags')`       | `[1, 2, 3]`       | `[[1, 2], [3]]`               |
| `['g' => [['u' => [['n' => 'A']]], ['u' => [['n' => 'B']]]]]` | `get('g.*.u')`  | `['n' => ['A', 'B']]` | `[[['n' => 'A']], [['n' => 'B']]]` |
| same                                                  | `get('g.*.u.*.n')`      | `['A', 'B']`      | `['A', 'B']` (unchanged)      |

To flatten a list of lists yourself:

```php
array_merge(...$dot->get('a.*.tags'));  // [1, 2, 3]
```

#### 3. A trailing `*` means the elements, not the array

A trailing `.*` used to be stripped, so `users.*` meant `users`.

| Data                          | Call                  | Before                     | Now                         |
|-------------------------------|-----------------------|----------------------------|-----------------------------|
| `['users' => [1, 2]]`         | `delete('users.*')`   | removes the `users` key    | `users` becomes `[]`        |
| `['a' => [1, 2]]`             | `set('a.*', 9)`       | `['a' => 9]`               | `['a' => [9, 9]]`           |
| `['users' => []]`             | `has('users.*')`      | `true`                     | `false`                     |
| `['r' => ['x' => 1, 'y' => 2]]` | `get('r.*')`        | `['x' => 1, 'y' => 2]`     | `[1, 2]` (keys dropped)     |
| `['a' => 1]`                  | `get('*')`            | `['a' => 1]`               | `[1]`                       |

If you meant the array itself, drop the `.*`: `delete('users')`, `set('a', 9)`, `has('users')`, `get('r')`, `all()`.

#### 4. Wildcards never create elements

| Data              | Call                              | Before                                | Now           |
|-------------------|-----------------------------------|---------------------------------------|---------------|
| `[]`              | `set('users.*.active', true)`     | `['users' => [['active' => true]]]`   | unchanged     |
| `['users' => []]` | `set('users.*.active', true)`     | `['users' => [['active' => true]]]`   | unchanged     |

To create the element, use its index: `set('users.0.active', true)`.

#### 5. Wildcard `has()` and `delete()`

- `has()` is `false` when the wildcard matches nothing (it was `true` for an empty array). When elements match, every one of them must still have the path, as before.
- Branches that don't reach the last `*` are skipped. With `groups.*.users.*.name`, a group without `users` used to make `has()` return `false`; now it is ignored, the same way `get()` ignores it.
- `delete()` returns `true` when at least one value was deleted. It used to return `false` unless every element had the key, even though it deleted the ones it found.

#### 6. Calls that used to throw now have a result

| Data                       | Call                  | Before                  | Now                              |
|----------------------------|-----------------------|-------------------------|----------------------------------|
| `['a' => 'hello']`         | `set('a.b', 1)`       | `TypeError`             | `['a' => ['b' => 1]]`            |
| `['a' => 5]`               | `set('a.b', 1)`       | `Error`                 | `['a' => ['b' => 1]]`            |
| `['a' => 1]`               | `count('x')`          | `TypeError`             | `0`                              |
| `['a' => 1]`               | `count('a')`          | `TypeError`             | `0`                              |
| `['i' => [1, ['b' => 2]]]` | `get('i.*.b')`        | `TypeError`             | `[2]` (non-arrays skipped)       |
| `['i' => [1, ['b' => 2]]]` | `has('i.*.b')`        | `TypeError`             | `true`                           |
| `['i' => [1, ['b' => 2]]]` | `set('i.*.b', 9)`     | `TypeError`             | `['i' => [1, ['b' => 9]]]`       |

If you caught these errors to detect a wrong shape, check the value instead, for example `is_array($dot->get('a'))`.

#### 7. Backslashes and `*` inside keys

- `\.`, `\*` and `\\` are now escapes: `get('config.app\.name')` reads the key `app.name`. A path that used these sequences for literal backslashes now means something else. Other backslashes are unchanged (`App\Models\User` is still one key).
- `*` is a wildcard only as a whole segment. `get('a.b*')` reads the key `b*`; it used to read `b`.

#### 8. Paths and keys that equal `0`

| Data            | Call             | Before                   | Now              |
|-----------------|------------------|--------------------------|------------------|
| `['a', 'b']`    | `get('0')`       | `['a', 'b']`             | `'a'`            |
| `['a', 'b']`    | `set('0', 'x')`  | nothing                  | `['x', 'b']`     |
| `['a', 'b']`    | `delete('0')`    | `false`, nothing deleted | `true`, deleted  |
| `[[1], [2]]`    | `toJson(0)`      | `'[[1],[2]]'`            | `'[1]'`          |
| `['', 'b']`     | `isEmpty('0')`   | `false` (whole array)    | `true`           |

#### 9. Deprecated inspection helpers

They now use `Pharaonic\Readable\Arr`, which gives a different result in two edge cases. `isNulledValues()` / `array_is_null()` are unchanged.

| Data          | Call                                                | Before  | Now    |
|---------------|-----------------------------------------------------|---------|--------|
| `[]`          | `isNumericKeys()`, `array_is_numeric($a)`           | `false` | `true` (same as PHP's `array_is_list()`) |
| `['a' => []]` | `isMultidimensional()`, `array_is_multidimensional($a)` | `false` | `true` (any array value counts, even an empty one) |

#### 10. Fixed bugs

- `delete('x.b')` with a missing `x` deleted the root key `b`. It now deletes nothing and returns `false`.
- `delete($int)` raised warnings and deleted nothing (2.x).

### Unchanged

- `get('')` returns the whole array, `has('')` is `true`, and `set('')` / `delete('')` do nothing.
- Leading and trailing dots and spaces are ignored, and `a..b` means an empty-string key.
- `toJson()` returns `''` when encoding fails; pass `JSON_THROW_ON_ERROR` to get an exception.
- `setArray()` after `setReference()` replaces the referenced array.
- `get()` returns the default for a missing path or a `null` value.
- Missing and `null` values inside a wildcard read are filled with the default: `get('users.*.email', '-')` gives `['a@x', '-']`.

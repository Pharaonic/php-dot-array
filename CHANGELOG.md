# Changelog

All notable changes to this project will be documented in this file.

## 8.5.2 - Unreleased

### Changed

- The documentation overview shows six feature cards, adding Reference Mode and ArrayAccess & Countable.

## 8.5.1 - 2026-10-08

The `8.5.x` line now runs the rebuilt 8.x path engine, with results identical to `8.4.1`. **`8.5.0` still had the 2.x engine, so this patch changes results for `8.5.0` users**: every change listed under [8.0.0](#800---2026-10-08) applies. Read [UPGRADE.md](UPGRADE.md#from-850-to-851) before updating.

### Changed

- **Requires PHP `>=8.5 <8.6`** (`8.5.0` allowed `^8.5`). Use the `8.4.x` line on PHP 8.4.
- `dot()` accepts `null` and another `DotArray` again (`8.5.0` accepted arrays only).
- Requires `pharaonic/php-readable` `~8.5.0`, its PHP 8.5 line.
- PHPStan analyses against PHP 8.5, and CI falls back to PHP 8.5 on branches that are not an `8.N.x` line.
- PHPStan `^2.1.22` is required for development, the first release that analyses against PHP 8.5.

## 8.4.1 - 2026-10-08

The `8.4.x` line now runs the rebuilt 8.x path engine, with results identical to `8.3.0`. **`8.4.0` still had the 2.x engine, so this patch changes results for `8.4.0` users**: every change listed under [8.0.0](#800---2026-10-08) applies. Read [UPGRADE.md](UPGRADE.md#from-840-to-841) before updating.

### Changed

- **Requires PHP `>=8.4 <8.5`** (`8.4.0` allowed `^8.4`). Use the `8.3.x` line on PHP 8.3 and the `8.5.x` line on PHP 8.5.
- Requires `pharaonic/php-readable` `~8.4.0`, its PHP 8.4 line.
- `dot()` accepts `null` and another `DotArray` again (`8.4.0` accepted arrays only).
- The test suite runs on PHPUnit 12, and deprecations still fail the run (`failOnDeprecation`).
- PHPStan `^2.1.18` is required for development: 1.x cannot parse PHP 8.4 syntax, and earlier 2.x releases lose track of the path parser's list type.
- The test suite calls methods on new instances without wrapping parentheses (`new DotArray([...])->get(...)`).
- PHPStan analyses against PHP 8.4, and CI falls back to PHP 8.4 on branches that are not an `8.N.x` line.

## 8.3.0 - 2026-10-08

The `8.3.x` line targets PHP 8.3. Results are identical to `8.2.0`. See [UPGRADE.md](UPGRADE.md#from-82-to-83).

### Changed

- **Requires PHP `>=8.3 <8.4`.** Use the `8.2.x` line on PHP 8.2.
- Requires `pharaonic/php-readable` `~8.3.0`, its PHP 8.3 line.
- PHPStan analyses against PHP 8.3, and CI falls back to PHP 8.3 on branches that are not an `8.N.x` line.
- The `ArrayAccess`, `Countable`, `IteratorAggregate` and `JsonSerializable` methods are marked with `#[\Override]`.

## 8.2.0 - 2026-10-08

The `8.2.x` line targets PHP 8.2. Results are identical to `8.1.0`. See [UPGRADE.md](UPGRADE.md#from-81-to-82).

### Changed

- **Requires PHP `>=8.2 <8.3`.** Use the `8.1.x` line on PHP 8.1.
- Requires `pharaonic/php-readable` `~8.2.0`, its PHP 8.2 line.
- PHPStan analyses against PHP 8.2, and CI falls back to PHP 8.2 on branches that are not an `8.N.x` line.

## 8.1.0 - 2026-10-08

The `8.1.x` line targets PHP 8.1. Results are identical to `8.0.0`. See [UPGRADE.md](UPGRADE.md#from-80-to-81).

### Changed

- **Requires PHP `>=8.1 <8.2`.** Use the `8.0.x` line on PHP 8.0.
- Requires `pharaonic/php-readable` `~8.1.0`, its PHP 8.1 line.
- PHPStan analyses against PHP 8.1, and CI falls back to PHP 8.1 on branches that are not an `8.N.x` line.

## 8.0.0 - 2026-10-08

Rebuilt the path engine. `get()`, `has()`, `set()`, `delete()` and `pull()` now share one path parser and one set of wildcard matching rules, documented in [`docs/`](docs/sections) and on [pharaonic.dev](https://pharaonic.dev/packages/php/dot-array).

The `8.0.x` line targets PHP 8.0. The API is unchanged from 2.x, but some results are different: see [UPGRADE.md](UPGRADE.md#from-2x-to-80).

### Added

- Depends on [`pharaonic/php-readable`](https://pharaonic.dev/packages/php/readable) (`~8.0.1`), which also requires `ext-mbstring`. The deprecated array inspection helpers now use its `Arr` class.
- `pull($path, $default = null)` returns a value and deletes it, wildcards included.
- Escaping: `\.` is a literal dot inside a key (`config.app\.name`), `\*` a literal `*` key (`items.\*.name`), and `\\` a literal backslash. Any other backslash is kept as is, so keys such as `App\Models\User` still work.
- `set()`, `clear()`, `setArray()` and `setReference()` return the instance, so calls can be chained.
- PHPStan (level max), PHP_CodeSniffer (PSR-12), Dependabot, and `composer test` / `analyse` / `lint` / `check` scripts.
- `SECURITY.md`, `SUPPORT.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`, `UPGRADE.md`, `FUNDING.yml`, and issue and pull request templates.
- Versioned documentation in `docs/` for pharaonic.dev.

### Changed

- **A value set to `null` exists.** `has()`, `delete()` and integer keys treat it as present. `get()` still returns the default for it, as before.
- **`get()` returns `[]` and arrays holding only `null`s** instead of replacing them with the default. Only a missing path or a `null` value gives the default.
- **Wildcard reads return one entry per matched element.** `get('users.*.profile')` returns `[['age' => 30], ['age' => 25]]`; it used to merge the profiles into `['age' => [30, 25]]`. Array values are no longer merged or flattened either (`get('a.*.tags')` gives `[[1, 2], [3]]`, was `[1, 2, 3]`). Nested wildcards still give one flat list: `get('groups.*.users.*.name')` is `['A', 'B', 'C']` as before.
- **A trailing `*` means the elements themselves.** `delete('users.*')` empties `users` (it used to delete the `users` key), `set('a.*', $v)` sets every element (it used to replace `a`), and `has('users.*')` is `false` for an empty array. `get('roles.*')` returns a list of the elements, without their keys.
- **Wildcards never create elements.** `set('users.*.active', true)` on a missing or empty `users` used to create `[['active' => true]]`; it now does nothing.
- `has()` with a wildcard is `false` when nothing matches (it used to be `true` for an empty array). When something matches, every matched element must still have the rest of the path, as before.
- `delete()` with a wildcard returns `true` when at least one value was deleted (it used to return `false` unless every element had the key).
- Elements that don't reach the last wildcard, or aren't arrays when keys follow it, are skipped by every operation. They used to throw a `TypeError`, and `has()` returned `false` when a branch lacked a key before the last wildcard (`groups.*.users.*.name` with a group that has no `users`).
- `set()` replaces a non-array value in the way of the path with an array (`set('a.b', 1)` on `['a' => 'hello']` gives `['a' => ['b' => 1]]`) instead of throwing an `Error`.
- `count($path)` returns `0` for a missing path or a non-array value instead of throwing a `TypeError`.
- `dot()` accepts the same values as the constructor (an array, another `DotArray`, or `null`).
- The helper functions are declared only when no function with the same name exists.
- `isNumericKeys()` / `array_is_numeric()` return `true` for an empty array (was `false`), matching PHP's `array_is_list()`.
- `isMultidimensional()` / `array_is_multidimensional()` return `true` when a value is an empty array, such as `['a' => []]` (was `false`).
- `toJson(0)` encodes the element at key `0`; it used to encode the whole array.
- `isEmpty('0')` checks the element at key `0`; it used to check the whole array.
- `*` is a wildcard only as a whole segment: `a.b*` is the literal key `b*` (the trailing `*` used to be stripped).
- **Requires PHP `>=8.0 <8.1`.** Each PHP version has its own release line (`8.0.x`, `8.1.x`, …).

### Fixed

- `delete('x.b')` with a missing `x` deleted the root key `b`.
- `delete($int)` did not delete and raised warnings.
- The path `'0'` was treated as an empty path: `get('0')` returned the whole array, and `set('0', $v)` and `delete('0')` did nothing.
- `has($int)` returned `null` instead of a boolean, for missing keys and for keys holding `null`.

### Deprecated

- `isNumericKeys()`, `isMultidimensional()`, `isNulledValues()`, and the `array_is_numeric()`, `array_is_multidimensional()` and `array_is_null()` helpers. They are not about dot-notation access and will be removed in a future major release. Use `Pharaonic\Readable\Arr::isList()`, `Arr::isMultidimensional()` and `Arr::isNull()` instead.
- The third argument of `get()`, `has()`, `set()` and `delete()` (an array to work on instead of the stored one). Use `dot($array)` or `setReference()` instead.

### Compatibility

- Public method names, parameters and declared return types are unchanged, so subclasses keep working. Methods that had no declared return type still have none; the fluent `$this` returns are documented in PHPDoc.
- `delete()` still returns `bool`.
- `toJson()` still returns an empty string when encoding fails; pass `JSON_THROW_ON_ERROR` to get a `JsonException`.
- `get('')` still returns the whole array and `has('')` is still `true`; `set('')` and `delete('')` still do nothing.
- Leading and trailing dots and spaces in a path are still ignored, and `a..b` still means an empty-string key.
- `setArray()` after `setReference()` still replaces the referenced array.

## 8.5.0 - 2026-04-09

### Changed

- Requires PHP `^8.5` and PHPUnit 13.
- Declared return types on `set()`, `setArray()`, `setReference()`, `clear()` and `all()`, and explicit nullable parameter types.
- `dot()` takes `array $arr = []`.

### Fixed

- `delete()` with an integer key.

## 8.4.0 - 2026-04-09

Same as 8.5.0, requiring PHP `^8.4`.

## 2.0.0 - 2023-05-13

### Added

- `isNumericKeys()`, `isNulledValues()` and `isMultidimensional()`.

### Changed

- Requires PHP 8.0 and declares return types.
- Improved `get()`, `has()`, `set()` and `delete()`.
- Renamed the helper functions to `array_is_numeric()`, `array_is_null()` and `array_is_multidimensional()`.

## 1.0.3 - 2023-01-14

### Fixed

- Added the missing `#[ReturnTypeWillChange]` attributes on the internal interface methods.

## 1.0.2 - 2022-07-10

### Changed

- PHP 8.1 support.

## 1.0.1 - 2022-02-14

### Added

- Test suite.

## 1.0.0 - 2020-11-07

- First release.

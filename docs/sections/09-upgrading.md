## Between 8.x Release Lines

Each `8.N.x` line targets exactly one PHP version: `8.0.x` runs on PHP 8.0, `8.1.x` on PHP 8.1, and so on. Moving from one 8.x line to another never changes the public API or results, so no code changes are needed. Composer picks the line that matches your PHP version.

## Upgrading from 8.5.0

:::warning Patch with result changes
`8.5.0` still ran the 2.x engine. `8.5.1` is a patch release, so `composer update` installs it automatically, but every change below applies. Review them first, or pin `8.5.0` with `composer require pharaonic/php-dot-array:8.5.0` until you have.
:::

## Upgrading from 2.x

{release.label} rebuilt the path engine. Every method and helper from 2.x still exists with the same parameters, and `delete()` still returns `bool`, but some results changed. Most of them were bugs.

The full list, with before and after values for every change, is in [UPGRADE.md]({package.githubUrl}/blob/8.5.x/UPGRADE.md).

### What Changed

| Change | Before | Now |
| --- | --- | --- |
| Existing `[]`, `[null, null]` | `get('a', 'x')` gave `'x'` | gives the value (`null` still gives `'x'`) |
| Existing `null` | `has()`/`delete()` ignored it | `has()` is `true`, `delete()` removes it |
| Wildcard values that are arrays | merged: `get('users.*.p')` gave `["e" => [1, 2]]` | one entry per element: `[["e" => 1], ["e" => 2]]` |
| Trailing `*` | stripped: `delete('users.*')` deleted `users` | the elements: `users` becomes `[]` |
| `set()` with a `*` on a missing or empty array | created `[["active" => true]]` | does nothing |
| `has()` with a `*` and no matches | `true` for an empty array | `false` |
| `delete()` with a `*` | `false` unless every element had the key | `true` when anything was deleted |
| Path `'0'` | treated as an empty path | the key `0` |
| `set('a.b', 1)` on `["a" => "hello"]`, `count('missing')`, wildcards over scalars | threw `TypeError` / `Error` | work (see [Basic Usage](#basic-usage) and [Wildcards](#wildcards)) |
| `\.`, `\*`, `\\` in a path | literal backslashes | escapes (see [Paths & Escaping](#paths)) |
| `isNumericKeys()` on `[]`, `isMultidimensional()` on `['a' => []]` | `false` | `true` (now backed by `Pharaonic\Readable\Arr`) |

### New

- `pull($path, $default)` returns a value and deletes it.
- `set()`, `clear()`, `setArray()` and `setReference()` return the instance, so calls can be chained.

### Deprecated

`isNumericKeys()`, `isMultidimensional()`, `isNulledValues()` and the `array_is_*()` helpers (use `Pharaonic\Readable\Arr` instead), and the third argument of `get()`, `has()`, `set()` and `delete()`. See the [API Reference](#api-reference) for replacements.

## Troubleshooting

### `get()` returns my default although the key exists

The key holds `null`, and `get()` gives the default for `null` values as well as for missing paths. Use `has()` to check whether the key exists:

```php
$dot = dot(['a' => null]);

$dot->get('a', 'x');   // "x"
$dot->has('a');        // true
```

### `get()` returns `[]` or `false` instead of my default

The key exists with that value. Only missing paths and `null` values give the default. Use `?:` for a fallback on any empty value:

```php
dot(['a' => []])->get('a') ?: 'x';   // "x"
```

### A key with a dot in its name can't be found

`config.app.name` looks for `["config"]["app"]["name"]`. Escape the dot: `config.app\.name`. In a double-quoted PHP string, write `"config.app\\.name"`.

### A wildcard read returns the default instead of a list

Nothing matched: the array before the `*` is missing, empty, or not an array, or no element reached the last `*`. Pass `[]` as the default when you want an empty list:

```php
dot(['users' => []])->get('users.*.name', []);   // []
```

### A wildcard read has fewer entries than the array has elements

When keys follow the last `*`, elements that aren't arrays are skipped. Branches that miss a key before the last `*` are skipped too. Elements that are arrays but miss the rest of the path are kept, with the default as their value (the same as a `null` value).

### `set()` with a wildcard didn't add anything

Wildcards only update existing elements; they never create them. Write to an index instead: `set('users.0.active', true)`.

### `has()` with a wildcard is `false` although some elements have the key

With a `*`, `has()` is `true` only when every matched element has the path. Check the values instead when one is enough:

```php
$emails = dot($data)->get('users.*.email', []);
$anyEmail = array_filter($emails, fn ($email) => $email !== null) !== [];
```

### `delete('users.*')` didn't remove the `users` key

A trailing `*` means the elements, so `users` becomes `[]`. Use `delete('users')` to remove the key.

### My original array didn't change

A `DotArray` works on its own copy. Use [Reference Mode](#reference-mode) to write to your array.

### `toJson()` returns an empty string

Encoding failed, usually because of invalid UTF-8. Pass `JSON_THROW_ON_ERROR` to see why:

```php
$dot->toJson(null, JSON_THROW_ON_ERROR);   // throws JsonException with the reason
```

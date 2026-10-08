## ArrayAccess & Interfaces

`DotArray` implements `ArrayAccess`, `Countable`, `IteratorAggregate` and `JsonSerializable`.

### ArrayAccess

Array syntax maps directly to the methods, paths and wildcards included:

| Syntax | Same as |
| --- | --- |
| `$dot['user.name']` | `$dot->get('user.name')` |
| `$dot['user.name'] = 'Raggi'` | `$dot->set('user.name', 'Raggi')` |
| `$dot[] = 'value'` | Appends to the root array. |
| `isset($dot['user.name'])` | `$dot->has('user.name')` |
| `unset($dot['user.name'])` | `$dot->delete('user.name')` |

```php
$dot = dot();

$dot['user.name'] = 'Raggi';
$dot['user.email'] = null;
$dot[] = 'appended';

$dot['user.name'];            // "Raggi"
isset($dot['user.email']);    // true (null still exists)
$dot['users.*.name'];         // null (no users yet)

unset($dot['user.name']);
```

:::info isset() and null
Unlike a plain array, `isset($dot['user.email'])` is `true` when the value is `null`, because it uses `has()`.
:::

### Countable

`count($dot)` counts the top-level items. `$dot->count($path)` counts the items at a path, and is `0` when the path is missing or not an array:

```php
$dot = dot(['users' => [['name' => 'A'], ['name' => 'B']], 'title' => 'x']);

count($dot);                   // 2
$dot->count('users');          // 2
$dot->count('users.*.name');   // 2
$dot->count('title');          // 0
```

### Iteration

`foreach` walks the top-level items:

```php
foreach (dot(['a' => 1, 'b' => 2]) as $key => $value) {
    echo "$key=$value ";   // a=1 b=2
}
```

### JSON

`toJson()` encodes the whole array, or the value at a path, with optional `json_encode()` flags. `json_encode($dot)` gives the same result as `toJson()`:

```php
$dot = dot(['user' => ['name' => 'Raggi', 'url' => 'https://pharaonic.dev']]);

$dot->toJson();                                 // the whole array
$dot->toJson('user.name');                      // '"Raggi"'
$dot->toJson('user', JSON_UNESCAPED_SLASHES);   // '{"name":"Raggi","url":"https://pharaonic.dev"}'
json_encode($dot);                              // same as toJson()
```

When encoding fails, `toJson()` returns an empty string. Pass `JSON_THROW_ON_ERROR` to get a `JsonException` instead.

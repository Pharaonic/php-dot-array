## Wildcards

A `*` segment matches every element of the array it is applied to. Every operation supports it:

```php
$dot = dot([
    'users' => [
        ['name' => 'Ahmed', 'profile' => ['email' => 'ahmed@example.com']],
        ['name' => 'Sara', 'profile' => []],
    ],
]);

$dot->get('users.*.name');                // ["Ahmed", "Sara"]
$dot->get('users.*.profile.email');       // ["ahmed@example.com", null]
$dot->get('users.*.profile.email', '-');  // ["ahmed@example.com", "-"]

$dot->has('users.*.name');                // true:  every user has a name
$dot->has('users.*.profile.email');       // false: Sara has no email

$dot->set('users.*.active', true);        // adds "active" => true to every user
$dot->delete('users.*.profile');          // removes "profile" from every user
$dot->count('users.*.name');              // 2
```

### Nested Wildcards

Nested wildcards give one flat list, in order:

```php
$dot = dot([
    'groups' => [
        ['users' => [['name' => 'A'], ['name' => 'B']]],
        ['users' => [['name' => 'C']]],
    ],
]);

$dot->get('groups.*.users.*.name');   // ["A", "B", "C"]

$dot->set('groups.*.users.*.active', true);
$dot->get('groups.*.users.*.active'); // [true, true, true]
```

### How Matching Works

The rules are the same for `get()`, `has()`, `set()`, `delete()` and `pull()`:

1. The elements reached by the **last** `*` are matched. For each of them, the rest of the path is read, written or deleted as usual.
2. Branches that don't reach the last `*` are skipped: a missing key, or a value that isn't an array, before it. When keys follow the last `*`, elements that aren't arrays are skipped too.
3. A trailing `*` means the elements themselves.
4. `*` is a wildcard only as a whole segment: `a.b*` is the literal key `b*`.

```php
$dot = dot([
    'groups' => [
        ['users' => [['name' => 'A']]],
        ['title' => 'no users'],          // skipped: no "users" key
        ['users' => [['name' => 'B'], 'x']], // "x" skipped: not an array
    ],
]);

$dot->get('groups.*.users.*.name');   // ["A", "B"]
$dot->has('groups.*.users.*.name');   // true
```

### Results and Defaults

| Operation | Result with a wildcard |
| --- | --- |
| `get($path, $default)` | A list with one entry per matched element: its value, or `$default` where the rest of the path is missing or `null`. `$default` itself when nothing matches. Keys of associative arrays are not kept. |
| `has($path)` | `true` when at least one element matches and every matched element has the rest of the path. |
| `set($path, $value)` | Sets the value in every matched element. Never creates elements for a `*`. |
| `delete($path)` | Deletes the key from every matched element; `true` when at least one was deleted. |
| `pull($path, $default)` | Returns what `get()` returns, then deletes like `delete()`. |
| `count($path)` | The number of entries `get()` returns, or `0`. |

```php
$dot = dot(['users' => [], 'roles' => ['admin' => ['level' => 1], 'editor' => ['level' => 2]]]);

$dot->get('users.*.name', 'none');   // "none" (nothing matched)
$dot->get('roles.*.level');          // [1, 2]
$dot->get('roles.*');                // [["level" => 1], ["level" => 2]]

$dot->set('users.*.active', true);   // nothing to update, nothing created
$dot->get('users');                  // []
```

### Trailing Wildcards

A trailing `*` reaches the elements themselves:

```php
$dot = dot(['flags' => ['beta' => true, 'dark' => true], 'tags' => ['a', 'b']]);

$dot->set('flags.*', false);   // every flag becomes false
$dot->delete('tags.*');        // "tags" becomes []
$dot->all();                   // ["flags" => ["beta" => false, "dark" => false], "tags" => []]
```

:::warning The array itself
`delete('tags.*')` empties `tags` but keeps the key. To remove the key, use `delete('tags')`.
:::

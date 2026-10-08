## Basic Usage

Wrap an array with the `dot()` helper, or create a `DotArray` yourself. Both accept an array, another `DotArray`, or nothing for an empty one:

```php
use Pharaonic\DotArray\DotArray;

$dot = dot(['user' => ['name' => 'Raggi']]);
$dot = new DotArray(['user' => ['name' => 'Raggi']]);
```

The instance works on its own copy of the array. To change your array directly, see [Reference Mode](#reference-mode).

### Reading Values

`get()` follows the path one key at a time. Numeric indexes are keys like any other:

```php
$dot = dot([
    'user'   => ['profile' => ['name' => 'Raggi']],
    'matrix' => [[1, 2], [3, 4]],
]);

$dot->get('user.profile.name');   // "Raggi"
$dot->get('user.profile');        // ["name" => "Raggi"]
$dot->get('matrix.1.0');          // 3
$dot->all();                      // the whole array

dot(['first', 'second'])->get(1); // "second" (integer keys work too)
```

### Default Values

The second argument of `get()` is returned when the path is missing or its value is `null`. A path is missing when a key doesn't exist, or when a value on the way isn't an array:

```php
$dot = dot(['user' => ['email' => null, 'name' => 'Raggi', 'active' => false]]);

$dot->get('user.phone');            // null   (missing, no default given)
$dot->get('user.phone', 'n/a');     // "n/a"  (missing)
$dot->get('user.name.first', 'x');  // "x"    ("Raggi" is not an array)
$dot->get('user.email', 'n/a');     // "n/a"  (null)
```

Other empty values, `false`, `0`, `''` and `[]`, are returned as they are:

```php
$dot->get('user.active', true);     // false
```

To tell a `null` value from a missing key, use `has()`.

:::info Wildcards and defaults
With a `*` in the path, the default works per element. See [Wildcards](#wildcards).
:::

### Writing Values

`set()` creates missing keys and returns the instance, so calls can be chained:

```php
$dot = dot();

$dot->set('user.name', 'Raggi')
    ->set('user.roles.0', 'admin');

$dot->all();   // ["user" => ["name" => "Raggi", "roles" => ["admin"]]]
```

A value that isn't an array and sits in the way of the path is replaced by an array:

```php
$dot = dot(['a' => 'hello']);
$dot->set('a.b', 1);

$dot->all();   // ["a" => ["b" => 1]]
```

### Checking Paths

`has()` is `true` when the path exists, even when its value is `null`:

```php
$dot = dot(['user' => ['email' => null]]);

$dot->has('user.email');   // true
$dot->has('user.phone');   // false
```

### Deleting Values

`delete()` returns `true` when something was deleted. `pull()` returns the value (or the default) and deletes it:

```php
$dot = dot(['token' => 'abc', 'user' => ['name' => 'Raggi', 'password' => 'secret']]);

$dot->delete('user.password');   // true
$dot->delete('user.password');   // false (already gone)

$dot->pull('token');             // "abc"
$dot->pull('token', 'none');     // "none"

$dot->all();                     // ["user" => ["name" => "Raggi"]]
```

### Clearing and Counting

```php
$dot = dot(['a' => [1, 2, 3], 'b' => 'x']);

$dot->count();       // 2 (top-level items)
$dot->count('a');    // 3
$dot->count('b');    // 0 (not an array)
$dot->isEmpty('a');  // false

$dot->clear();
$dot->isEmpty();     // true
```

An empty path never deletes anything; use `clear()` to empty the whole array.

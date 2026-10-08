## Reference Mode

By default, a `DotArray` works on its own copy, so your array is never changed:

```php
$array = ['user' => ['name' => 'Old']];

$dot = dot($array);
$dot->set('user.name', 'New');

$array['user']['name'];   // "Old"
```

`setReference()` binds the instance to your array instead, so every write lands there:

```php
use Pharaonic\DotArray\DotArray;

$array = ['user' => ['name' => 'Old']];

$dot = (new DotArray())->setReference($array);
$dot->set('user.name', 'New');

$array['user']['name'];   // "New"
```

`set()`, `delete()`, `pull()`, `clear()`, `setArray()` and ArrayAccess writes, wildcards included, all write to the referenced array. Changes you make to the array yourself are visible through the instance too. Reads never modify it.

```php
$config = ['users' => [['name' => 'A', 'password' => 'x']]];

(new DotArray())->setReference($config)
    ->set('users.*.active', true)
    ->delete('users.*.password');

$config;   // ["users" => [["name" => "A", "active" => true]]]
```

:::warning setArray() and clear()
After `setReference()`, `setArray($items)` replaces the contents of your array and `clear()` empties it. To stop working on your array, create a new instance.
:::

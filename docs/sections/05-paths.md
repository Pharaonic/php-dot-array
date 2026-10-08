## Paths & Escaping

A path is a list of keys separated by `.`. The same parser is used by every method.

### Escaping Dots

Use `\.` for a dot that is part of a key:

```php
$dot = dot(['config' => ['app.name' => 'Pharaonic']]);

$dot->get('config.app\.name');   // "Pharaonic"
$dot->get('config.app.name');    // null (looks for ["app"]["name"])

$dot->set('hosts.example\.com.port', 443);
$dot->get('hosts');              // ["example.com" => ["port" => 443]]
```

### Escaping Wildcards

Use `\*` for a key that is literally `*`:

```php
$dot = dot(['items' => ['*' => ['name' => 'Literal Star'], 'x' => ['name' => 'X']]]);

$dot->get('items.\*.name');   // "Literal Star"
$dot->get('items.*.name');    // ["Literal Star", "X"]
```

### Backslashes

`\\` is a literal backslash. Any other backslash is kept as it is, so class names work as keys:

```php
$dot = dot(['App\Models\User' => ['table' => 'users']]);

$dot->get('App\Models\User.table');   // "users"
```

:::info PHP strings
In single-quoted PHP strings, `'\.'` and `'\*'` are already a backslash followed by the character, so you can write the paths exactly as shown. In double-quoted strings, write `"config.app\\.name"`.
:::

### Numeric Keys

Numeric segments reach numeric keys. PHP itself stores `'1'` as the integer key `1`, so both spellings reach the same element, while `'01'` stays a string key:

```php
$dot = dot([0 => 'A', '1' => 'B', '01' => 'C']);

$dot->get('1');    // "B"
$dot->get(1);      // "B"
$dot->get('01');   // "C"
$dot->get('0');    // "A"
```

### Unusual Paths

| Path | Meaning |
| --- | --- |
| `''`, `'.'` | The whole array: `get()` returns it, `has()` is `true`, `set()` and `delete()` do nothing. |
| `'.user.'`, `' user '` | Leading and trailing dots and spaces are ignored: `user`. |
| `'user..name'` | An empty-string key between `user` and `name`. |
| `'a\.'` | The key `a.` (an escaped trailing dot is kept). |

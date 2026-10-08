## Examples

### 1. Reading an API Response

Pull what you need from a deeply nested JSON response without `isset()` chains:

```php title="app/Services/OrderImporter.php"
$response = dot(json_decode($json, true));

$orderId  = $response->get('data.order.id');
$currency = $response->get('data.order.currency', 'USD');
$skus     = $response->get('data.order.items.*.sku', []);
$total    = array_sum($response->get('data.order.items.*.price', []));
```

The `[]` default keeps `array_sum()` safe when the order has no items.

### 2. Cleaning Data Before Output

Remove sensitive fields from every record, then encode the result:

```php title="app/Http/Controllers/UserController.php"
$payload = dot(['users' => $users])
    ->set('users.*.profile.public', true);

$payload->delete('users.*.password');
$payload->delete('users.*.remember_token');

return $payload->toJson('users', JSON_UNESCAPED_UNICODE);
```

### 3. Editing a Config Array in Place

Use reference mode to update a configuration array that the rest of the code already holds:

- ===Bootstrap

  ```php title="bootstrap/config.php"
  use Pharaonic\DotArray\DotArray;

  $config = require __DIR__ . '/../config/app.php';

  $settings = (new DotArray())->setReference($config);
  $settings->set('cache.driver', getenv('CACHE_DRIVER') ?: 'file');
  $settings->set('mail.hosts.smtp\.example\.com.port', 587);
  ```

- ===Config

  ```php title="config/app.php"
  return [
      'cache' => ['driver' => 'array'],
      'mail'  => ['hosts' => []],
  ];
  ```

After this, `$config['cache']['driver']` holds the new driver, and the SMTP host is stored under the key `smtp.example.com`.

### 4. Validating Nested Input

Check that every line of a submitted form has the fields you need:

```php title="app/Forms/InvoiceForm.php"
$input = dot($_POST);

if (! $input->has('lines.*.product_id') || ! $input->has('lines.*.quantity')) {
    throw new InvalidArgumentException('Every line needs a product and a quantity.');
}

$input->set('lines.*.tax_rate', 0.14);
```

`has()` with a wildcard is `false` when there are no lines at all, and when any line misses the field.

### 5. Array Syntax in Templates

Pass a `DotArray` to code that expects array access:

```php
$view = dot(['page' => ['title' => 'Home', 'meta' => ['og.title' => 'Welcome']]]);

echo $view['page.title'];             // Home
echo $view['page.meta.og\.title'];    // Welcome
echo isset($view['page.subtitle']) ? 'yes' : 'no';   // no
```

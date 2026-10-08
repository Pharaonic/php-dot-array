## Installation

Install the package with Composer. There is nothing to register or configure.

### Requirements

- PHP 8.0.x (each `8.x` release line targets the matching PHP version)
- `ext-mbstring`
- `pharaonic/php-readable` ~8.0.1 (installed automatically)

### Composer Installation

```bash title="Terminal" no-line-numbers
composer require pharaonic/php-dot-array
```

Composer autoloads the `Pharaonic\DotArray` namespace and the global `dot()` helper.

:::success Installation Complete
You're all set! Try `dot(['user' => ['name' => 'Raggi']])->get('user.name')`, which returns `"Raggi"`.
:::

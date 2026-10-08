:::badges
- PHP Package {color=blue}
- {release.label} {color=green}
- {package.license} License {color=purple}
:::

# Dot Array

Read, write, check and delete values in deeply nested PHP arrays using dot-notation paths (`user.profile.name`) and `*` wildcards (`users.*.email`). It is small and framework-independent. One path parser and one set of wildcard rules are shared by every operation, so `get()`, `set()`, `has()` and `delete()` always agree on what a path means.

:::features
### Dot-Notation Paths {icon="code-brackets"}
`get('user.profile.name')`, `set('a.b.c', 1)`: missing keys are created on write.

### Wildcards {icon="grid"}
`users.*.email` reaches the value in every element, nested wildcards included.

### Safe Defaults {icon="check-circle"}
`get('user.phone', 'n/a')` falls back for missing and `null` values; `has()` still sees a `null` key.

### Escaping {icon="pencil"}
`config.app\.name` reads the key `app.name`; `items.\*` reads the key `*`.

### Reference Mode {icon="switch"}
`setReference($array)` writes every change straight to your own array.

### ArrayAccess & Countable {icon="lines"}
`$dot['user.name']`, `isset()`, `unset()`, `count()`, iteration and `json_encode()` all work.
:::

:::info Quick Tip
Wrap any array with the `dot()` helper and chain your writes: `dot($data)->set('user.active', true)->delete('user.password')`.
:::

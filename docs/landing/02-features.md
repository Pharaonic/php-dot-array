---
view: components.packages.features
variant: compact
badge: Key Features
title: Everything you need to work with nested arrays
subtitle: Wrap an array with dot() and use one small, predictable API.
items:
  - icon: code-brackets
    title: Dot-Notation Paths
    text: "`get('user.profile.name')`, `set('a.b.c', 1)`. Missing keys are created for you on write."
  - icon: grid
    title: Wildcards
    text: "`users.*.email` reads, updates, checks or deletes the value in every element, nested wildcards included."
  - icon: check-circle
    title: Safe Defaults
    text: "`get()` falls back to your default for missing paths and `null` values, while `has()` still sees a key set to `null`."
  - icon: pencil
    title: Escaping
    text: "`config.app\\.name` reads a key with a dot in it, and `items.\\*` a key that is literally `*`."
  - icon: switch
    title: Reference Mode
    text: Point it at your own array with `setReference()` and every change lands there.
  - icon: lines
    title: ArrayAccess & Countable
    text: "`$dot['user.name']`, `isset()`, `unset()`, `count()`, iteration and `json_encode()` all work."
---

---
view: components.home.faq
badge: FAQ
title: "{package.name}"
highlight: Questions
subtitle: "Quick answers about installing and using {package.name}."
---

## What is {package.name}?

{card.description} It's a free, open-source {technology.name} package by Pharaonic.

## How do I install {package.name}?

Run `composer require {package.composer}` in your project's root directory.

## What does {package.name} require?

The latest release requires {package.requiresText}.

## Does it depend on Laravel or another framework?

No. {package.name} is plain PHP; its only dependency is `pharaonic/php-readable`, another framework-independent Pharaonic package, so it works in any project. It doesn't use or replace Laravel's `Arr` or `data_get()`.

## What does a wildcard return?

A list with one entry per matched element, so `get('users.*.name')` gives `["Ahmed", "Sara"]`. Nested wildcards give one flat list, and elements where the rest of the path is missing or `null` get the default.

## How do I read a key that contains a dot?

Escape the dot with a backslash: `get('config.app\.name')` reads the key `app.name`. Use `\*` for a key that is literally `*`.

## Is {package.name} free to use?

Yes. {package.name} is open source under the {package.license} license, so you can use it in personal and commercial projects.

## Where can I find the {package.name} documentation?

Read the [{package.name} documentation]({package.docsUrl}) for setup and usage examples.

## How do I report a bug or contribute to {package.name}?

Open an issue or a pull request on [GitHub]({package.githubUrl}), or ask in the [Pharaonic Discord](https://discord.gg/XQG9RhvEvf).

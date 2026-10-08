---
view: components.packages.quick-look
title: A quick look
subtitle: Plain paths for one value, wildcards for all of them.
file: app/Support/UserPayload.php
language: php
code: |
  $dot = dot($payload);

  $dot->get('users.0.profile.email');   // "ahmed@example.com"
  $dot->get('users.*.name');            // ["Ahmed", "Sara"]
  $dot->get('users.1.phone', 'n/a');    // "n/a"

  $dot->set('users.*.active', true)
      ->set('meta.synced_at', time());

  $dot->delete('users.*.password');
  $dot->has('users.*.profile.email');   // true
---

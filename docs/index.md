---
name: Dot Array

action:
  label: View on Packagist
  href: "{package.packagistUrl}"

views: components.packages

breadcrumbs:
  - label: Home
    href: route:home
  - label: Packages
    href: route:packages.index
  - label: "{technology.name} Packages"
    href: "url:/packages/{technology.slug}"
  - label: "{package.name}"

card:
  topic: data
  icon: grid
  tags: array dot notation nested arrays wildcard asterisk arrayaccess config json
  description: Read, write, check and delete values in deeply nested PHP arrays with dot-notation paths like user.profile.name and wildcards like users.*.email.

seo:
  title: "{package.fullName} - Dot Notation & Wildcards for Nested PHP Arrays"
  description: "{package.name} is a PHP package for reading, writing, checking and deleting values in deeply nested arrays with dot-notation paths and * wildcards. {package.downloadsShort}+ downloads, {package.license} licensed."
  keywords: php dot notation, php array dot notation, nested array php, array wildcard php, php array get set, dot array, arrayaccess, php array path
  author: Pharaonic
  images:
    - "{package.cover}"
  openGraph:
    type: website
    siteName: Pharaonic
  twitter:
    card: summary_large_image

schema:
  "@type": SoftwareSourceCode
  name: "{package.name}"
  description: "{package.name} is a PHP package for reading, writing, checking and deleting values in deeply nested arrays with dot-notation paths and * wildcards."
  image: "{package.cover}"
  codeRepository: "{package.githubUrl}"
  programmingLanguage: PHP
  runtimePlatform: "{technology.name}"
  version: "{package.version}"
  datePublished: "{package.publishedAt}"
  dateModified: "{package.updatedAt}"
  license: "https://opensource.org/licenses/{package.license}"
  isAccessibleForFree: true
  sameAs:
    - "{package.githubUrl}"
    - "{package.packagistUrl}"
  author:
    "@id": url:/#organization
  publisher:
    "@id": url:/#organization
  interactionStatistic:
    "@type": InteractionCounter
    interactionType: https://schema.org/DownloadAction
    userInteractionCount: "{package.downloads}"
---

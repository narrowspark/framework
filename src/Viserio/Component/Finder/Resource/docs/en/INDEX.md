# The Finder Component

- [Installation](#installation)
- [Introduction](#introduction)
- [Available Methods](#available_methods)

<a name="installation"></a>
## Installation

```bash
$  composer require viserio/finder
```

<a name="introduction"></a>
## Introduction

Narrowspark provides a powerful finder component, find files and directories based on different criteria (name, file size, modification time, etc.) via an intuitive [fluent interface][1].
For example, check out the following code. We'll use the `Finder` class to find files and/or directories:
```php
use Viserio\Component\Finder\Finder;

$finder = new Finder();
// find all files in the current directory
$finder->files()->in(__DIR__);

// check if there are any search results
if ($finder->hasResults()) {
    // ...
}

foreach ($finder as $file) {
    $absoluteFilePath = $file->getRealPath();
    $fileNameWithExtension = $file->getRelativePathname();

    // ...
}
```
The `$file` variable is an instance of `Viserio\Component\Finder\SplFileInfo` which extends PHP's own `SplFileInfo` to provide methods to work with relative paths.

> The `Finder` object doesn't reset its internal state automatically. This means that you need to create a new instance if you do not want to get mixed results.

<a name="available_methods"></a>
## Available Methods

[1]: https://en.wikipedia.org/wiki/Fluent_interface

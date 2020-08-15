<h2 align="center">PHP Constants Exporter :zap:</h2>

## Introduction

PHP Constants Exporter provides a simple way to re-use your php constants on the frontend; Javascript.

## Requirements

PHP 7.1+

## Basic Example
```php
<?php
use Therealsmat\ConstantsExporter;

$constants = [
    Months::class => 'js/',
    PayoutTypes::class => 'js/'
];

(new ConstantsExporter)
    ->setConstants($constants)
    ->export();
```

`$constants` is an array of `key => value` pairs. The key is the constants file: the source, while the value is the destination. 

A valid destination is:

- An existing directory; or
- A filename.

If the destination is a directory, then the directory must exist. A filename same as the PHP constants filename would be generated with a `.js` extension.

Your constants would be exported safely to your destination, and ready for use.

### Multiple Executions
If you export the same constant to the same destination multiple times, the existing content would be overwritten.
 
This can be useful for keeping the backend and frontend constants in sync.
 
### Excluding Inherited Constants
If your source file extends a base class (e.g an ORM), you may only export its own constants by calling the `excludeInheritedConstants()` method.

```php
<?php
use Therealsmat\ConstantsExporter;

$constants = [
    Months::class => 'js/',
    PayoutTypes::class => 'js/'
];

(new ConstantsExporter)
    ->setConstants($constants)
    ->excludeInheritedConstants()
    ->export();
```

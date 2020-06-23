Command Bus
---------------

This is easy to use PHP command bus implementation.

[![Version][version-badge]][version-link]
[![Total Downloads][downloads-badge]][downloads-link]
[![Php][php-badge]][php-link]
[![License][license-badge]](LICENSE)
[![Build Status][build-badge]][build-link]

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require onliner/command-bus:^1.0
```

or add this code line to the `require` section of your `composer.json` file:

```
"onliner/command-bus": "^1.0"
```

Usage
-----

```php
use Onliner\CommandBus\Builder;

class Hello
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

$dispatcher = (new Builder())
    ->handle(Hello::class, function (Hello $command) {
        echo 'Hello ' . $command->message;
    })
    ->build();

$dispatcher->dispatch(new Hello('onliner'));
```

More examples can be found [here](examples).

License
-------

Released under the [MIT license](LICENSE).


[version-badge]:    https://img.shields.io/packagist/v/onliner/command-bus.svg
[version-link]:     https://packagist.org/packages/onliner/command-bus
[downloads-link]:   https://packagist.org/packages/onliner/command-bus
[downloads-badge]:  https://poser.pugx.org/onliner/command-bus/downloads.png
[php-badge]:        https://img.shields.io/badge/php-7.2+-brightgreen.svg
[php-link]:         https://www.php.net/
[license-badge]:    https://img.shields.io/badge/license-MIT-brightgreen.svg
[build-link]:       https://github.com/onliner/command-bus/actions?workflow=test
[build-badge]:      https://github.com/onliner/command-bus/workflows/test/badge.svg

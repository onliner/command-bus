Command Bus
---------------

This is a PHP library that wraps up the server-side verification step required
to process responses from the [GeeTest](https://www.geetest.com) service. 

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
composer require onliner/commandbus:^1.0
```

or add this code line to the `require` section of your `composer.json` file:

```
"onliner/commandbus": "^1.0"
```

Usage
-----

```php
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;

class Hello
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

$bus = (new Builder())
    ->handle(Hello::class, function (Hello $command) {
        echo 'Hello ' . $command->message;
    })
    ->build();

$bus->dispatch(new Hello('onliner'));
```

License
-------

Released under the [MIT license](LICENSE).


[version-badge]:    https://img.shields.io/packagist/v/onliner/commandbus.svg
[version-link]:     https://packagist.org/packages/onliner/commandbus
[downloads-link]:   https://packagist.org/packages/onliner/commandbus
[downloads-badge]:  https://poser.pugx.org/onliner/commandbus/downloads.png
[php-badge]:        https://img.shields.io/badge/php-7.2+-brightgreen.svg
[php-link]:         https://www.php.net/
[license-badge]:    https://img.shields.io/badge/license-MIT-brightgreen.svg
[build-link]:       https://github.com/onliner/commandbus/actions?workflow=test
[build-badge]:      https://github.com/onliner/commandbus/workflows/test/badge.svg

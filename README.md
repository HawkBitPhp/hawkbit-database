# Hawkbit Database

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-coveralls]][link-coveralls]

Object orientated database handling with POPO's, unit of work, identity map and data mapper. 

## Install

### Using Composer

Hawkbit Database is available on [Packagist][link-packagist] and can be installed using [Composer](https://getcomposer.org/). This can be done by running the following command or by updating your `composer.json` file.

```bash
composer require hawkbit/database
```

composer.json

```javascript
{
    "require": {
        "hawkbit/database": "~1.0"
    }
}
```

Be sure to also include your Composer autoload file in your project:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

### Downloading .zip file

This project is also available for download as a `.zip` file on GitHub. Visit the [releases page](https://github.com/hawkbit/database/releases), select the version you want, and click the "Source code (zip)" download button.

### Requirements

The following versions of PHP are supported by this version.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Usage

View [Examples](example) for usage.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email <mjls@web.de> instead of using the issue tracker.

## Credits

- [Marco Bunge](https://github.com/mbunge)
- [All contributors](https://github.com/hawkbit/database/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hawkbit/database.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/HawkBitPhp/hawkbit-database/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hawkbit/database.svg?style=flat-square
[ico-coveralls]: https://img.shields.io/coveralls/HawkBitPhp/hawkbit-database/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hawkbit/hawkbit
[link-travis]: https://travis-ci.org/HawkBitPhp/hawkbit
[link-downloads]: https://packagist.org/packages/hawkbit/hawkbit
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
[link-coveralls]: https://coveralls.io/github/HawkBitPhp/hawkbit

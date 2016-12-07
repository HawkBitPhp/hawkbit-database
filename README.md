# Hawkbit Persistence

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]
[![Coverage Status][ico-coveralls]][link-coveralls]

Persistence layer for Hawkbit PSR-7 Micro PHP framework.
Features unit of work, identity map, object graph, popo's and mapper. 

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

This project is also available for download as a `.zip` file on GitHub. Visit the [releases page](https://github.com/hawkbit/persistence/releases), select the version you want, and click the "Source code (zip)" download button.

### Requirements

The following versions of PHP are supported by this version.

* PHP 5.5
* PHP 5.6
* PHP 7.0
* HHVM

## Setup

Create a Mapper and an entity. See 

Create a Connection and register mappers

```php
<?php

use Hawkbit\Storage\ConnectionManager;
use Application\Persistence\Mappers\PostMapper;

$connection = ConnectionManager::create([
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);

$connection->getMapperLocator()->register(PostMapper::class);
```

Load Mapper by mapper class or entity class

```php
<?php

use Application\Persistence\Mappers\PostMapper;
use Application\Persistence\Entities\Post;

// load by mapper
$mapper = $connection->loadMapper(PostMapper::class);

// load by entity
$mapper = $connection->loadMapper(Post::class);

```

## Data manipulation

### Create entity

```php
<?php

use Application\Persistence\Entities\Post;

$entity = new Post();

$entity->setContent('cnt');

/** @var Post $createdEntity */
$mapper->create($entity);

```


### Load entity

```php
<?php

$entity = $mapper->find(['id' => 1]);

```


### Update entity

```php
<?php

$entity->setContent('FOO');
$mapper->update($entity);

```

### Delete entity

```php
<?php

$mapper->delete($entity);

```

## Transactions

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
- [All contributors](https://github.com/hawkbit/persistence/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hawkbit/persistence.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/HawkBitPhp/hawkbit-persistence/master.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hawkbit/persistence.svg?style=flat-square
[ico-coveralls]: https://img.shields.io/coveralls/HawkBitPhp/hawkbit-persistence/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/hawkbit/hawkbit
[link-travis]: https://travis-ci.org/HawkBitPhp/hawkbit
[link-downloads]: https://packagist.org/packages/hawkbit/hawkbit
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
[link-coveralls]: https://coveralls.io/github/HawkBitPhp/hawkbit

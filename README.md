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
* PHP 7.1
* HHVM

## Usage

### Connect and go!

1. Register a connection
2. Register a Mapper if you need to work with Unit of Work and Mappers
3. Have fun!

### Examples

We also provide [Examples](/example) for following documentation.

### Connections 

```php
<?php
use Hawkbit\Database\ConnectionManager;

// setup connection
$connection = ConnectionManager::create([
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);
```

#### Shared Connections

In huge applications, you need to be able to access connections at many points. Simply share your connection

```php
<?php
use Hawkbit\Database\ConnectionManager;

ConnectionManager::getInstance()->add($connection);
```

You may want to save time or lines of code and add connection directly

```php
<?php
use Hawkbit\Database\ConnectionManager;

ConnectionManager::getInstance()->add([
  'url' => 'sqlite:///:memory:',
  'memory' => 'true'
]);
```

and access connection at another point of your application

```php
<?php
use Hawkbit\Database\ConnectionManager;

ConnectionManager::getInstance()->get();

```

##### Multiple connections

In some cases you need two ore more connections. Therefore connections could have a name, the default name is *default* 
(`Hawkbit\Database\ConnectionManager::DEFAULT_CONNECTION`).

```php
<?php
use Hawkbit\Database\ConnectionManager;

// add connections
ConnectionManager::getInstance()->add([
  'url' => 'sqlite:///:memory:',
  'memory' => 'true'
]);

ConnectionManager::getInstance()->add([
  'url' => 'mysql://<user>:<password>@<host>/<database>?charset=utf8',
], 'second');

// and access connections

$default = ConnectionManager::getInstance()->get();
$second = ConnectionManager::getInstance()->get('second');

```

##### Prefixing

On a shared database it is use full to to prefix tables with a specific name, e.g. application abbreviation. You need to 
set the prefix on your connection. The system is prefixing all table names automatically.

Add PHP 7.1 support

```php
<?php

// setup prefixes
$connection->setPrefix('custom_');
$gateway = $connection->createGateway('user'); // connects to custom_user table

```

As you can see you (as do mapper) pass the tablename and internally the tablename will be prefixed.  

#### Migration

##### Easy schema setup with SQL

```php
<?php
use Hawkbit\Database\ConnectionManager;

// add connections
$connection = ConnectionManager::getInstance()->get();

// setup schema
$connection->exec('CREATE TABLE post (id int, title VARCHAR(255), content TEXT, date DATETIME DEFAULT CURRENT_DATE )');
```

##### Advanced Migration with phinx

Schema migration is not provided by this Package. We recommend packages like [Phinx](http://phinx.org) for advanced migration.

### Gateway

The gateway mediates between database and mapper. It is able to execute table or view specific operations and provides CRUD queries. 

Create a new gateway from connection

```php
<?php

$gateway = $connection->createGateway('post');

```

#### Create

Create a new entry

```php
<?php
// create a new post
$gateway->create()
    ->setValue('title', 'Anything')
    ->setValue('content', 'Lorem Ipsum')
    ->execute();
```

#### Read (select)
Fetch entries as array (refer to `\PDO::FETCH_ASSOC`). Entries do have following array representation

```php
<?php
$posts = [
    0 => [
        'id' => 1,
        'title' => 'Anything',
        'content' => 'Lorem Ipsum',
    ],
    1 => [
        'id' => 2,
        'title' => 'Anything else',
        'content' => 'More text',
    ]
];
```

```php
<?php

use Doctrine\DBAL\Types\Type;

$posts = $gateway
    ->select()
    ->where('title = ?')
    ->setParameter(0, 'Anything', Type::STRING)
    ->execute()
    ->fetchAll();

// process entries
foreach ($posts as $post){
    echo sprintf('<h1>%s</h1><p>%s</p>', $post['title'], $post['content']);
}
```

You could also use doctrines expression builder combined with `QueryBuilder::createPositionalParameter` and 
`QueryBuilder::createNamedParameter` for more complex queries!

#### Update

Update an entry

```php
<?php

$gateway
    ->update()
    ->set('content', 'Cool text instead of Lorem Ipsum, but Lorem Ipsum is cool at all!')
    ->where('id = 1')
    ->execute();
```

#### Delete

Delete an entry

```php
<?php

$gateway
    ->delete()
    ->where('id = 1')
    ->execute();
```

### Mapper

#### Data mapper

The [data mapper](http://www.martinfowler.com/eaaCatalog/dataMapper.html) is mapping data to entities and extracting 
data from entities. The mapper is also aware of database table configuration and converts data types into both 
directions - PHP and database. It utilizes an [Identity Map]() which ensures that an object is only load once. 

[Example mapper configuration](/example/Persistence/Mappers/PostMapper.php)

It is recommended to extend the mapper with custom logic for accessing associated data or other specific data sets. 
*Data association / Data relation are not supported at this point!*

The combination of mapper and entities may have the flavor of 
[Table-Data-Gateway](http://www.martinfowler.com/eaaCatalog/tableDataGateway.html) with passive records.  

#### Entities

Entities represent a single entry (row) of a database table or view. It is a set of Setters and Getters, without any 
logic - passive record. Entities are Plain Old PHP Objects (POPO)

[Example entity](/example/Persistence/Entities/Post.php)

#### Prepare and load mappers

Register mapper to mapper locator to preload configuration and ensure access. An Exception is thrown when locator is 
unable to find a mapper.

```php
<?php


use Application\Persistence\Mappers\PostMapper;

// register mappers
$connection->getMapperLocator()->register(PostMapper::class);
```

Locate a registered mapper

```php
<?php

use Application\Persistence\Entities\Post;

$entity = new Post();
$mapper = $connection->loadMapper($entity);

```

Locate a registered mapper by entity

```php
<?php

use Application\Persistence\Entities\Post;

// entity instance is also allowed
$mapper = $connection->loadMapper(Post::class);

// or create entity from mapper
```

Create entity from mapper, if you don't want to create a new instance of entity

```php
<?php
$entity = $mapper->createEntity();

```

#### Modify data

The mapper is able determine entity state and save (create or update) data. The mapper is modifying the same entity instance.
All changes which have been made in database are also stored into the entity at the same time. 

##### Create

Create an entry

```php
<?php

// create entity
$entity->setContent('cnt');
$mapper->create($entity);
```

##### Update

Update an entry

```php
<?php

$entity->setContent('FOO');
$mapper->update($entity);
```

##### Save

The mapper is also able to detect a new or existing entity and choose to create or update an related entry in database.
 
```php
<?php

$entity->setContent('FOO');
$entity->setTitle('Philosophy of bar, baz & foo');

// create or update
$mapper->save($entity);
```

#### Delete

Delete an entry

```php
<?php
// delete entity
$mapper->delete($entity);
```

#### Fetching data

##### Find

Find entry by primary key or compound key and return a single entity result or false 

```php
<?php
 
$post = $mapper->find(['id' => 1]);
```

##### Custom select

Fetch entries as a list of entities. Entries do have following representation

```php
<?php
$posts = [
    0 => new Post(), // 'id': 1, 'title': 'Anything', 'content': 'Lorem Ipsum'
    1 => new Post(), // 'id': 2, 'title': 'Anything else', 'content': 'More text'
];
```

A select is taking all valid kinds of callable to modify the query.

```php
<?php

use \Doctrine\DBAL\Query\QueryBuilder;

$posts = $mapper->select(function(QueryBuilder $queryBuilder){
    // build even more complex queries
    $queryBuilder->where('id = 1');
});

// process entries

/** @var \Application\Persistence\Entities\Post $post */
foreach ($posts as $post){
    echo sprintf('<h1>%s</h1><p>%s</p>', $post->getTitle(), $post->getContent());
}
```

Fetch only one entry will return an entity instead of an entity list

```php
<?php

use \Doctrine\DBAL\Query\QueryBuilder;

$one = true;
$post = $mapper->select(function(QueryBuilder $queryBuilder){
    // build even more complex queries
    $queryBuilder->where('id = 1');
}, ['*'], $one);
```

### Transactions (Unit of Work)

For data consistency you want to use transactions. Basically all queries are wrapped with a transaction and automatically 
committed if auto-commit option is enabled. You could also create, update and delete a huge set of data and commit them a once.

#### Build-in doctrine transaction

To use a unit of work, you need to register a mapper. In some cases, like prototyping you want use gateway for quick access. 
Therefore you should use build-in transaction.

```php
<?php

try {
    $connection->beginTransaction();
    
    $gateway->update()->set('content', 'blah')->where('id = 1');
    $gateway->update()->set('content', 'blub')->where('id = 2');
    $gateway->delete()->where('id in(32,45,16)');
    $gateway->create()->setValue('content', 'i am new!')->execute();
    
    $connection->commit();

} catch (\Exception $e) {
    $connection->rollBack();
    
    // you may want to pass exception to next catch
    throw $e;
}
```

#### Unit of Work - Transaction with mappers

Transactions need registered mappers to work correctly.

Modify you data in any order, unit of work is sorting the execution of create, update and delete for you. If all 
modifications are done, you need to execute `UnitOfWork::commit` Please refer to the following example of unit of work:

```php
<?php

use Application\Persistence\Entities\Post;

$unitOfWork = $connection->createUnitOfWork();
$entity = new Post();

// or create entity from mapper
//$entity = $mapper->createEntity();

// create entity
$entity->setContent('cnt');
$unitOfWork->create($entity);

// commit transaction
if(false === $unitOfWork->commit()){
    //handle exception
    $unitOfWork->getException();

    // get last processed entity
    $unitOfWork->getLastProcessed();
}

// get all modified entities
$unitOfWork->getModified();

// get a list of all entities by state
$unitOfWork->getProcessed();

// find entity by primary key or compound key
$mapper = $connection->loadMapper($entity);
$mapper->find(['id' => 1]);

// update entity
$entity->setContent('FOO');
$unitOfWork->update($entity);

// delete entity
$unitOfWork->delete($entity);

// commit transaction, again
$unitOfWork->commit();
```

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

[link-packagist]: https://packagist.org/packages/hawkbit/hawkbit-database
[link-travis]: https://travis-ci.org/HawkBitPhp/database
[link-downloads]: https://packagist.org/packages/hawkbit/hawkbit-database
[link-author]: https://github.com/mbunge
[link-contributors]: ../../contributors
[link-coveralls]: https://coveralls.io/github/HawkBitPhp/hawkbit-database

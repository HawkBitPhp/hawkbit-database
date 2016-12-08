<?php

use Application\Persistence\Entities\Post;
use Application\Persistence\Mappers\PostMapper;
use Hawkbit\Database\ConnectionManager;

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * Tis is only an example. All steps are separated in their specific logic
 */

// setup connection
$connection = ConnectionManager::create([
    'url' => 'sqlite:///:memory:',
    'memory' => 'true'
]);

// setup schema
$connection->exec('CREATE TABLE post (id int, title VARCHAR(255), content TEXT, date DATETIME DEFAULT CURRENT_DATE )');

// register mappers
$connection->getMapperLocator()->register(PostMapper::class);

// load mapper
$entity = new Post();
$mapper = $connection->loadMapper($entity);

// create entity
$entity->setContent('cnt');
$mapper->create($entity);

// find entity by primary key or compound key
$mapper->find(['id' => 1]);

// update entity
$entity->setContent('FOO');
$mapper->update($entity);

// delete entity
$mapper->delete($entity);
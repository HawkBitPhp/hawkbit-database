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
$mapper = $connection->loadMapper(Post::class);
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
$mapper->find(['id' => 1]);

// update entity
$entity->setContent('FOO');
$unitOfWork->update($entity);

// delete entity
$unitOfWork->delete($entity);

// commit transaction
$unitOfWork->commit();
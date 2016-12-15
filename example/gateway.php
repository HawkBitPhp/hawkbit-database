<?php

use Doctrine\DBAL\Types\Type;
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

$gateway = $connection->createGateway('post');

// create a new post
$gateway->create()
    ->setValue('title', 'Anything')
    ->setValue('content', 'Lorem Ipsum')
    ->execute();

// find posts as array
// you could also use ExpressionBuilder and QueryBuilder::createPositionalParameter / QueryBuilder::createNamedParameter
// for more complex queries
$posts = $gateway
    ->select()
    ->where('title = ?')
    ->setParameter(0, 'Anything', Type::STRING)
    ->execute()
    ->fetchAll();

// $posts = [
//  0 => [
//    'id' => 1,
//    'title' => 'Anything',
//    'content' => 'Lorem Ipsum',
//  ]
//]

foreach ($posts as $post){
    echo sprintf('<h1>%s</h1><p>%s</p>', $post['title'], $post['content']);
}

// update post
$gateway
    ->update()
    ->set('content', 'Cool text instead of Lorem Ipsum, but Lorem Ipsum is cool at all!')
    ->where('id = 1')
    ->execute();

// delete post
$gateway
    ->delete()
    ->where('id = 1')
    ->execute();

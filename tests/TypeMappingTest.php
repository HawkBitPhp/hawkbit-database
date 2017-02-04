<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 04.02.2017
 * Time: 17:27
 */

namespace Hawkbit\Database\Tests;


use Hawkbit\Database\Connection;
use Hawkbit\Database\ConnectionManager;
use Hawkbit\Database\Tests\Stubs\JsonEntity;
use Hawkbit\Database\Tests\Stubs\JsonMapper;


class TypeMappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function tearDown()
    {
        $this->connection->exec('DROP TABLE json_documents');
    }

    protected function setUp()
    {
        $connection = ConnectionManager::create([
            'url' => 'sqlite:///:memory:',
            'memory' => true
        ]);

        $connection->getMapperLocator()->register(JsonMapper::class);

        $connection->exec('CREATE TABLE json_documents (id INTEGER PRIMARY KEY AUTOINCREMENT, data TEXT, name VARCHAR, namespace VARCHAR)');
        $connection->exec('INSERT INTO json_documents (data, name, namespace) VALUES ("[]", "hello-world", "blog/post")');

        $this->connection = $connection;
    }

    public function testNewState()
    {
        $mapper = $this->connection->loadMapper(JsonEntity::class);
        $document = new JsonEntity();
        $document
            ->setData([
                'content' => 'Some Text'
            ])
            ->setName('Foo bar')
            ->setNamespace('blog/post');

        // force is new check
        $this->assertTrue($mapper->isNew($document));

        $fetchedDocument = $mapper->find(['id' => 1]);
        $this->assertFalse($mapper->isNew($fetchedDocument));


    }
}

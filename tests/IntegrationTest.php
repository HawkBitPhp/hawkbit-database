<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 18.10.2016
 * Time: 15:07
 */

namespace Hawkbit\Storage\Tests;

use Doctrine\DBAL\Types\Type;
use Hawkbit\Storage\AbstractMapper;
use Hawkbit\Storage\Connection;
use Hawkbit\Storage\ConnectionManager;
use Hawkbit\Storage\Tests\Stubs\PostEntity;
use Hawkbit\Storage\Tests\Stubs\PostMapper;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

    protected function tearDown()
    {
        $this->connection->exec('DROP TABLE post');
    }

    protected function setUp()
    {
        $connection = ConnectionManager::create([
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $connection->getMapperLocator()->register(PostMapper::class);

        $connection->exec('CREATE TABLE post (id INTEGER PRIMARY KEY, content TEXT)');

        $this->connection = $connection;
    }


    public function testIntegration()
    {
        $connection = $this->connection;
        $entity = new PostEntity();

        /** @var PostMapper $mapper */
        $mapper = $connection->loadMapper($entity);

        $this->assertNotEmpty($mapper->getPrimaryKey());
        $this->assertContains('id', $mapper->getPrimaryKey());
        $this->assertInstanceOf($mapper->getEntityClass(),$entity);
        $this->assertEquals('post', $mapper->getTableName());
        $this->assertArrayHasKey('id', $mapper->getColumns());
        $this->assertArrayHasKey('content', $mapper->getColumns());
        $this->assertEquals('id', $mapper->getLastInsertIdReference());

        $entity->setContent('cnt');
        $this->assertEquals(null, $entity->getId());

        /** @var PostEntity $createdEntity */
        $createdEntity = $mapper->create($entity);
        $graph = $connection->getIdentityStateGraph();

        $this->assertEquals($connection->lastInsertId(), $entity->getId());
        $this->assertEquals($entity, $createdEntity);
        $this->assertEquals($createdEntity->getContent(), 'cnt');
        $this->assertEquals($entity->getContent(), 'cnt');
        $this->assertEquals($entity->getId(), $createdEntity->getId());
        $this->assertInternalType('integer', $entity->getId());
        $this->assertEquals(1, $entity->getId());

        $foundEntity = $mapper->find(['id' => $entity->getId()]);

        $this->assertEquals($entity, $foundEntity);
        $this->assertEquals($entity->getId(), $foundEntity->getId());

        $entity->setContent('FOO');

        /** @var PostEntity $updatedEntity */
        $updatedEntity = $mapper->update($entity);
        $graph = $connection->getIdentityStateGraph();

        $this->assertEquals($entity, $updatedEntity);
        $this->assertEquals($updatedEntity->getContent(), 'FOO');
        $this->assertEquals($entity->getContent(), 'FOO');
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
        $this->assertInternalType('integer', $entity->getId());
        $this->assertEquals(1, $entity->getId());

        $mapper->delete($entity);

        $graph = $connection->getIdentityStateGraph();

        if(true){

        }
    }
}

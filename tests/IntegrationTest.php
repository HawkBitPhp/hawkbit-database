<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 18.10.2016
 * Time: 15:07
 */

namespace Hawkbit\Database\Tests;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Hawkbit\Database\Connection;
use Hawkbit\Database\ConnectionManager;
use Hawkbit\Database\Tests\Stubs\PostEntity;
use Hawkbit\Database\Tests\Stubs\PostMapper;

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
        $connection->exec('CREATE TABLE user (id INTEGER PRIMARY KEY, username VARCHAR)');

        $this->connection = $connection;
    }


    public function testMapperIntegration()
    {
        $connection = $this->connection;
        $entity = new PostEntity();

        /** @var PostMapper $mapper */
        $mapper = $connection->loadMapper($entity);

        $this->assertNotEmpty($mapper->getPrimaryKey());
        $this->assertContains('id', $mapper->getPrimaryKey());
        $this->assertInstanceOf($mapper->getEntityClass(), $entity);
        $this->assertEquals('post', $mapper->getTableName());
        $this->assertArrayHasKey('id', $mapper->getColumns());
        $this->assertArrayHasKey('content', $mapper->getColumns());
        $this->assertEquals('id', $mapper->getLastInsertIdReference());

        $entity->setContent('cnt');
        $this->assertEquals(null, $entity->getId());

        /** @var PostEntity $createdEntity */
        $createdEntity = $mapper->create($entity);

        // test returned entity and entity are equal
        $this->assertEquals($connection->lastInsertId(), $entity->getId());
        $this->assertEquals($entity, $createdEntity);
        $this->assertEquals($createdEntity->getContent(), 'cnt');
        $this->assertEquals($entity->getContent(), 'cnt');
        $this->assertEquals($entity->getId(), $createdEntity->getId());
        $this->assertInternalType('integer', $entity->getId());
        $this->assertEquals(1, $entity->getId());

        $foundEntity = $mapper->find(['id' => $entity->getId()]);

        // test object has been found in database
        $foundResult = $mapper->getGateway()->select()->where('id = ' . $entity->getId())->execute()->fetch();
        $this->assertEquals((int)$foundResult['id'], $foundEntity->getId());
        $this->assertEquals($foundResult['content'], $foundEntity->getContent());

        // test object has been reused from in memory
        $this->assertEquals($entity, $foundEntity);
        $this->assertEquals($entity->getId(), $foundEntity->getId());

        $entity->setContent('FOO');

        // test entity has been modified in database and in memory
        /** @var PostEntity $updatedEntity */
        $updatedEntity = $mapper->update($entity);

        $this->assertEquals($entity, $updatedEntity);
        $this->assertEquals($updatedEntity->getContent(), 'FOO');
        $this->assertEquals($entity->getContent(), 'FOO');
        $this->assertEquals($entity->getId(), $updatedEntity->getId());
        $this->assertInternalType('integer', $entity->getId());
        $this->assertEquals(1, $entity->getId());

        // test entity has been modified in database
        $updatedResult = $mapper->getGateway()->select()->where('id = ' . $entity->getId())->execute()->fetch();
        $this->assertEquals((int)$updatedResult['id'], $updatedEntity->getId());
        $this->assertEquals($updatedResult['content'], $updatedEntity->getContent());

        $mapper->delete($entity);
    }

    public function testUoWIntegration()
    {
        $connection = $this->connection;
        /** @var PostMapper $mapper */
        $mapper = $connection->loadMapper(PostEntity::class);
        $unitOfWork = $connection->createUnitOfWork();

        $contentFromAnyOtherSource = [
            'Hello 0',
            'Hello 1',
            'Hello 2',
        ];

        foreach ($contentFromAnyOtherSource as $content) {
            /** @var PostEntity $entity */
            $entity = $mapper->createEntity();
            $entity->setContent($content);
            $unitOfWork->create($entity);
        }

        $this->assertTrue($unitOfWork->commit());

        // test find commited entities
        foreach ($contentFromAnyOtherSource as $content) {
            $foundEntity = $mapper->select(function (QueryBuilder $query) use ($content) {
                $query->where($query->expr()->eq('content', $query->createPositionalParameter($content, Type::STRING)));
            }, ['*'], true);

            // test object has been found in database
            $foundResult = $mapper->getGateway()->select()->where('content = ?')->setParameter(0, $content, Type::STRING)->execute()->fetch();
            $this->assertEquals((int)$foundResult['id'], $foundEntity->getId());
            $this->assertEquals($foundResult['content'], $foundEntity->getContent());
        }
    }
}

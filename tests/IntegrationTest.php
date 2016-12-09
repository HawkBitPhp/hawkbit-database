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
use Hawkbit\Database\Tests\Stubs\UserEntity;
use Hawkbit\Database\Tests\Stubs\UserMapper;

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
        $connection->getMapperLocator()->register(UserMapper::class);

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
        /** @var PostMapper $postMapper */
        $postMapper = $connection->loadMapper(PostEntity::class);

        /** @var UserMapper $userMapper */
        $userMapper = $connection->loadMapper(UserEntity::class);
        $unitOfWork = $connection->createUnitOfWork();

        $contentFromAnyOtherSource = [
            'Hello 0',
            'Hello 1',
            'Hello 2',
        ];

        $userFromAnyOtherSource = [
            'Jake',
            'Andy',
            'Dave',
        ];

        foreach ($contentFromAnyOtherSource as $content) {
            /** @var PostEntity $entity */
            $entity = $postMapper->createEntity();
            $entity->setContent($content);
            $unitOfWork->create($entity);
        }

        foreach ($userFromAnyOtherSource as $content) {
            /** @var UserEntity $entity */
            $entity = $userMapper->createEntity();
            $entity->setUsername($content);
            $unitOfWork->create($entity);
        }

        $this->assertTrue($unitOfWork->commit());

        // test find commited entities
        foreach ($contentFromAnyOtherSource as $content) {
            /** @var PostEntity $foundPostEntity */
            $foundPostEntity = $postMapper->select(function (QueryBuilder $query) use ($content) {
                $query->where($query->expr()->eq('content', $query->createPositionalParameter($content, Type::STRING)));
            }, ['*'], true);

            // test object has been found in database
            $foundResult = $postMapper->getGateway()->select()->where('content = ?')->setParameter(0, $content, Type::STRING)->execute()->fetch();
            $this->assertEquals((int)$foundResult['id'], $foundPostEntity->getId());
            $this->assertEquals($foundResult['content'], $foundPostEntity->getContent());
        }

        // test find commited entities
        foreach ($userFromAnyOtherSource as $user) {

            /** @var UserEntity $foundUserEntity */
            $foundUserEntity = $userMapper->select(function (QueryBuilder $query) use ($user) {
                $query->where($query->expr()->eq('username', $query->createPositionalParameter($user, Type::STRING)));
            }, ['*'], true);

            // test object has been found in database
            $foundResult = $userMapper->getGateway()->select()->where('username = ?')->setParameter(0, $user, Type::STRING)->execute()->fetch();
            $this->assertEquals((int)$foundResult['id'], $foundUserEntity->getId());
            $this->assertEquals($foundResult['username'], $foundUserEntity->getUsername());
        }

        $firstPost = $postMapper->find(['id' => 1]);
        /** @var PostEntity $secondPost */
        $secondPost = $postMapper->find(['id' => 2]);
        $secondPost->setContent('Bam');
        $thirdPost = new PostEntity();
        $thirdPost->setContent('FooBar');

        $firstUser = $userMapper->find(['id' => 1]);
        /** @var UserEntity $secondUser */
        $secondUser = $userMapper->find(['id' => 2]);
        $secondUser->setUsername('Andrew');
        $thirdUser = new UserEntity();
        $thirdUser->setUsername('Rudy');

        $unitOfWork->delete($firstUser);
        $unitOfWork->delete($firstPost);
        $unitOfWork->update($secondUser);
        $unitOfWork->update($secondPost);
        $unitOfWork->create($thirdUser);
        $unitOfWork->create($thirdPost);

        $unitOfWork->commit();

        if(true){

        }

    }
}

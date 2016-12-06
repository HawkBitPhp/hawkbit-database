<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 18.10.2016
 * Time: 15:07
 */

namespace Hawkbit\Storage\Tests;


use ContainerInteropDoctrine\EntityManagerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hawkbit\Application;
use Hawkbit\Persistence\PersistenceService;
use Hawkbit\Persistence\PersistenceServiceInterface;
use Hawkbit\Persistence\PresentationServiceProvider;
use Hawkbit\Storage\ConnectionManager;
use Hawkbit\Storage\Tests\Stubs\PostEntity;
use Hawkbit\Storage\Tests\Stubs\PostMapper;
use League\Plates\Engine;
use org\bovigo\vfs\vfsStream;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{

    public function testIntegration()
    {
        $connection = ConnectionManager::create([
            'url' => 'sqlite:///:memory:',
            'memory' => 'true'
        ]);

        $connection->exec('CREATE TABLE post (id int, title VARCHAR(255), content TEXT, date DATETIME DEFAULT CURRENT_DATE )');

        $mapper = new PostMapper($connection);
        $entity = new PostEntity();

        $this->assertNotEmpty($mapper->getPrimaryKey());
        $this->assertContains('id', $mapper->getPrimaryKey());
        $this->assertInstanceOf($mapper->getEntityClass(),$entity);
        $this->assertEquals('post', $mapper->getTableName());
        $this->assertArrayHasKey('id', $mapper->getColumns());
        $this->assertArrayHasKey('content', $mapper->getColumns());
        $this->assertEquals('id', $mapper->getLastInsertIdReference());

        $entity->setContent('cnt');

        /** @var PostEntity $resultEntity */
        $resultEntity = $mapper->create($entity);
//        $mapper->find();

        $this->assertEquals($connection->lastInsertId(), $resultEntity->getId());


        $connection->exec('DROP TABLE post');





    }
}

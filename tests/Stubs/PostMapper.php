<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:05
 */

namespace Hawkbit\Storage\Tests\Stubs;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Hawkbit\Storage\AbstractMapper;

class PostMapper extends AbstractMapper
{
    public function define()
    {
        $this->tableName = 'post';
        $this->columns = [
            new Column('id', Type::getType(Type::INTEGER)),
            new Column('content', Type::getType(Type::TEXT)),
        ];
        $this->primaryKey = ['id'];
        $this->entityClass = PostEntity::class;
    }
}
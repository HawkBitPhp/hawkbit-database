<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:05
 */

namespace Hawkbit\Database\Tests\Stubs;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Hawkbit\Database\AbstractMapper;

class UserMapper extends AbstractMapper
{
    public function define()
    {
        $this->tableName = 'user';
        $this->columns = [
            new Column('id', Type::getType(Type::INTEGER)),
            new Column('username', Type::getType(Type::TEXT)),
        ];
        $this->primaryKey = ['id'];
        $this->entityClass = UserEntity::class;
    }
}
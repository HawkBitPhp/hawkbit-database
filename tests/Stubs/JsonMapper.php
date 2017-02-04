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

class JsonMapper extends AbstractMapper
{
    public function define()
    {
        $this->tableName = 'json_documents';
        $this->columns = [
            new Column('id', Type::getType(Type::INTEGER)),
            new Column('name', Type::getType(Type::STRING)),
            new Column('namespace', Type::getType(Type::STRING)),
            new Column('data', Type::getType(Type::JSON_ARRAY))
        ];
        $this->primaryKey = ['id'];
        $this->entityClass = JsonEntity::class;
    }
}
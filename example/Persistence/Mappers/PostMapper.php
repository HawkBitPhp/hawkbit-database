<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 07.12.2016
 * Time: 09:44
 */

namespace Application\Persistence\Mappers;


use Application\Persistence\Entities\Post;
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
        $this->entityClass = Post::class;
    }
}
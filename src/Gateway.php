<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:29
 */

namespace Hawkbit\Database;

/**
 * Grant low-level access to data base
 * @package Hawkbit\Database
 */
final class Gateway
{

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var
     */
    private $table;
    /**
     * @var null
     */
    private $alias;

    /**
     * Gateway constructor.
     * @param Connection $connection
     * @param $table
     * @param null $alias
     */
    public function __construct(Connection $connection, $table, $alias = null)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @param array $fields
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function select($fields = ['*'])
    {
        return $this->createQueryBuilder()->select($fields)->from($this->table, $this->alias);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function create()
    {
        return $this->createQueryBuilder()->insert($this->table);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function update()
    {
        return $this->createQueryBuilder()->update($this->table, $this->alias);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function delete()
    {
        return $this->createQueryBuilder()->delete($this->table, $this->alias);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->connection->createQueryBuilder();
    }

}
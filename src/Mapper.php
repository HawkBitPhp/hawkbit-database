<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 12:11
 */

namespace Hawkbit\Database;


use Doctrine\DBAL\Schema\Column;

interface Mapper
{
    /**
     * Repository constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection);

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * Get entity class
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return string
     */
    public function getTableNameAlias();

    /**
     * @return string[]
     */
    public function getPrimaryKey();

    /**
     * @return Column[]
     */
    public function getColumns();

    /**
     * @return string
     */
    public function getAutoIncrementKey();

    /**
     * @return Gateway
     */
    public function getGateway();

    /**
     * @return Hydrator
     */
    public function getHydrator();

    /**
     * @return IdentityMap
     */
    public function getIdentityMap();

    /**
     * Find entity by primary key
     *
     * @param [] $primaryKey
     * @return object[]|object
     */
    public function find($primaryKey = []);

    /**
     * Find entity by criteria callback
     *
     * $collection = $repository->select(function(QueryBuilder $query){
     *  $query->where('id = 1');
     * });

     * @param callable $queryCallback
     * @param array $fields
     * @return \object[]
     */
    public function select(callable $queryCallback, $fields = ['*']);

    /**
     * Delete an entity or a list of entities
     *
     * @param object[]|object $entity
     * @return int
     */
    public function delete($entity);


    /**
     * Save an entity or a list of entities
     *
     * @param object[]|object $entity
     * @return int
     */
    public function create($entity);


    /**
     * Save an entity or a list of entities
     *
     * @param object[]|object $entity
     * @return int
     */
    public function update($entity);

    /**
     * Save an entity or a list of entities
     *
     * @param object[]|object $entity
     * @return int
     */
    public function save($entity);
}
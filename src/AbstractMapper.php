<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 15:25
 */

namespace Hawkbit\Storage;


use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

abstract class AbstractMapper implements Mapper
{

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string[]
     */
    protected $primaryKey;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var string
     */
    protected $lastInsertIdReference;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * Repository constructor.
     * @param Connection $connection
     * @param Hydrator $hydrator
     */
    public function __construct(Connection $connection, Hydrator $hydrator = null)
    {
        $this->connection = $connection;
        $this->hydrator = $hydrator ? $hydrator : new Hydrator();

        $this->doDefine();

        $this->gateway = new Gateway($this->connection, $this->getTableName());
        $this->identityMap = $connection->loadIdentityMap($this->getEntityClass());
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Gateway
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @return Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Get entity class
     *
     * @return string
     */
    final public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Get table name
     *
     * @return string
     */
    final public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * primary key by name
     *
     * first key will also always determined as last_insert_id key
     *
     * @return string[]
     */
    final public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    final public function getLastInsertIdReference()
    {
        return $this->lastInsertIdReference;
    }

    /**
     * @return Column[]
     */
    final public function getColumns()
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[$column->getName()] = $column;
        }
        return $columns;
    }

    /**
     * @param null $id
     * @return object
     */
    final public function createEntity($id = null){
        $identityMap = $this->getIdentityMap();
        if($identityMap->hasId($id)){
            return $identityMap->getObject($id);
        }
        $class = $this->getEntityClass();
        return new $class;
    }

    /**
     * Check if given blob is entity
     *
     * @param $blob
     * @return bool
     */
    public function isEntity($blob)
    {
        if (!is_object($blob)) {
            return false;
        }
        $instance = $this->getEntityClass();
        if (!($blob instanceof $instance)) {
            return false;
        }

        return true;
    }

    /**
     * @param object|array $dataOrEntity
     * @return bool
     */
    public function isNew($dataOrEntity)
    {
        if ($this->isEntity($dataOrEntity)) {
            $dataOrEntity = $this->extract($dataOrEntity);
        }

        // iterate primary keys
        // if any key is empty, the entity is new
        foreach ($this->getPrimaryKey() as $key) {
            $empty = true === empty($dataOrEntity[$key]);
            if ($empty) {
                return true;
            }
        }
        return false;
    }

    /**
     * Find entity by primary key
     *
     * $repository->find(['id' => 1]);
     *
     * @param [] $primaryKey
     * @return object[]|object
     */
    public function find($primaryKey = [])
    {
        // load entity from cache
        if(isset($primaryKey[$this->getLastInsertIdReference()])){
            if($this->getIdentityMap()->hasId($primaryKey[$this->getLastInsertIdReference()])){
                return $this->getIdentityMap()->getObject($primaryKey[$this->getLastInsertIdReference()]);
            }
        }

        return $this->select(function (QueryBuilder $queryBuilder) use ($primaryKey) {
            $expressionBuilder = $queryBuilder->expr();
            $expression = [];
            $keys = $this->getPrimaryKey();

            // build from valid primary keys
            foreach ($keys as $key) {
                if (!isset($primaryKey[$key])) {
                    continue;
                }

                $expression[] = $expressionBuilder->eq($key, $queryBuilder->createPositionalParameter($primaryKey[$key]));
            }

            $queryBuilder->where(call_user_func_array([$expressionBuilder, 'andX'], $expression));
        }, ['*'], true);
    }

    /**
     * Find entity by criteria callback
     *
     * $repository->findBy(function(QueryBuilder $query){
     *  $query->where('id = 1');
     * });
     *
     * @param callable $queryCallback
     * @param array $fields
     * @param bool $one
     * @return object[]|object
     */
    public function select(callable $queryCallback, $fields = ['*'], $one = false)
    {
        $query = $this->gateway->select($fields);

        call_user_func_array($queryCallback, [&$query]);

        if (true === $one) {
            $query->setMaxResults(1);
        }
        $recordSet = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        if (0 === count($recordSet)) {
            return true === $one ? null : [];
        }

        $set = [];
        foreach ($recordSet as $record) {
//            if(isset($record[$this->getLastInsertIdReference()])){
//                if($this->getIdentityMap()->hasId($record[$this->getLastInsertIdReference()])){
//                    $set[] = $this->getIdentityMap()->getObject($record[$this->getLastInsertIdReference()]);
//                    continue;
//                }
//            }
            $set[] = $this->map($record);
        }

        reset($set);

        $result = true === $one ? current($set) : $set;

        return $result;
    }

    /**
     * Get a collection of all entities
     *
     * @param object $entity
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function delete($entity)
    {
        $query = $this->gateway->delete();
        $expressionBuilder = $query->expr();
        $expression = [];
        $columns = $this->getColumns();
        $keys = $this->getPrimaryKey();
        $data = $this->extract($entity);

        // build from valid primary keys
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                continue;
            }

            $type = isset($columns[$key]) ? $columns[$key]->getType()->getName() : \PDO::PARAM_STR;
            $expression[] = $expressionBuilder->eq($key, $query->createPositionalParameter($data[$key], $type));
        }

        $query->where(call_user_func_array([$expressionBuilder, 'andX'], $expression));

        $result = $query->execute();

        $this->getIdentityMap()->removeId($data[$this->getLastInsertIdReference()]);
        $this->getConnection()->getObjectGraph()->remove($entity);

        return $result;
    }

    /**
     * @param $entity
     * @return object
     */
    public function create($entity)
    {
        $columns = $this->getColumns();
        $data = $this->extract($entity);

        $query = $this->gateway->create();
        // prepare insert statement set values
        foreach ($data as $key => $value) {
            $type = isset($columns[$key]) ? $columns[$key]->getType()->getName() : \PDO::PARAM_STR;
            $query->setValue($key, $query->createPositionalParameter($value, $type));
        }

        $query->execute();

        //inject auto increment key
        if (null !== $this->getLastInsertIdReference()) {
            $data[$this->getLastInsertIdReference()] = $this->connection->lastInsertId();
            $entity = $this->map($data, $entity);

            // add identity
            $this->getIdentityMap()->set($data[$this->getLastInsertIdReference()], $entity);
        }

        $this->getConnection()->getObjectGraph()->add($entity);

        return $entity;
    }

    /**
     * @param $entity
     * @return mixed
     */
    public function update($entity)
    {
        $columns = $this->getColumns();
        $data = $this->extract($entity);
        $query = $this->gateway->update();

        // set values
        foreach ($data as $key => $value) {
            $type = isset($columns[$key]) ? $columns[$key]->getType()->getName() : Type::STRING;
            $query->set($key, $query->createPositionalParameter($value, $type));
        }

        // extract primary keys and pass to where condition
        $expressionBuilder = $query->expr();
        $expression = [];
        $keys = $this->getPrimaryKey();

        // build condition from primary key
        foreach ($keys as $key) {
            $type = isset($columns[$key]) ? $columns[$key]->getType()->getName() : Type::STRING;
            if (!isset($data[$key])) {
                continue;
            }
            $expression[] = $expressionBuilder->eq($key, $query->createPositionalParameter($data[$key], $type));
        }

        // build and execute condition
        $query->where(call_user_func_array([$expressionBuilder, 'andX'], $expression));
        $query->execute();

        // update identity
        $this->getIdentityMap()->set($data[$this->getLastInsertIdReference()], $entity);
        $this->getConnection()->getObjectGraph()->modify($entity);

        // we don't need to update entity data
        return $entity;
    }

    /**
     * Save new or existing entity
     *
     * @param object[]|object $entity
     * @return int
     */
    public function save($entity)
    {
        $new = $this->isNew($entity);
        return $new ? $this->create($entity) : $this->update($entity);
    }

    /**
     * Hydrate data to entity and respect data types
     *
     * @param $data
     * @param null $entity
     * @return object
     */
    protected function map($data, $entity = null)
    {
        // process entity
        $columns = $this->getColumns();
        if (false === $this->isEntity($entity)) {
            $reflection = new \ReflectionClass($this->getEntityClass());
            $entity = $reflection->newInstance();
        }

        foreach ($columns as $column) {
            $name = $column->getName();

            if (!isset($data[$name])) {
                continue;
            }

            $data[$name] = $column->getType()->convertToPHPValue($data[$name], $this->connection->getDatabasePlatform());
        }

        return $this->hydrator->hydrate($data, $entity);
    }

    /**
     * Extract data from entity and respect data types
     * @param $entity
     * @return array
     */
    protected function extract($entity)
    {
        $columns = $this->getColumns();
        $class = $this->getEntityClass();

        if (!$this->isEntity($entity)) {
            throw new \InvalidArgumentException('Object needs to be an instance of ' . $class);
        }

        $data = $this->hydrator->extract($entity);

        foreach ($columns as $column) {
            $name = $column->getName();

            if (!isset($data[$name])) {
                continue;
            }

            $data[$name] = $column->getType()->convertToPHPValue($data[$name], $this->connection->getDatabasePlatform());
        }

        return $data;
    }

    /**
     * @return mixed
     */
    abstract protected function define();

    /**
     *
     */
    private function doDefine()
    {
        $this->define();

        // sanitize and validate entity class
        if (!class_exists($this->entityClass)) {
            throw new \InvalidArgumentException('Unable top find entity! ' . $this->entityClass);
        }

        // sanitize and validate primary key
        if (empty($this->primaryKey)) {
            $this->primaryKey = [];
        }
        if (is_scalar($this->primaryKey)) {
            $this->primaryKey = [$this->primaryKey];
        }
        if (is_array($this->primaryKey)) {
            $primaryKey = [];
            foreach ($this->primaryKey as $key => $value) {
                if (is_scalar($value) || null === $value) {
                    $primaryKey[$key] = $value;
                }
            }
            $this->primaryKey = $primaryKey;
        }
        if (!is_array($this->primaryKey)) {
            throw new \InvalidArgumentException('Invalid Primary key! expect array');
        }

        reset($primaryKey);

        // sanitize and validate last insert id reference which is a subset of primary key
        if (0 < count($this->primaryKey) && null === $this->lastInsertIdReference) {
            $this->lastInsertIdReference = current($primaryKey);
        }

        //sanitize and validate columns
        $columns = [];
        foreach ($this->columns as $column) {
            if ($column instanceof Column) {
                $columns[$column->getName()] = $column;
            }
        }
        $this->columns = $columns;

        //sanitize and validate table name
        if (empty($this->tableName)) {
            $this->tableName = Inflector::tableize(get_class($this->entityClass));
        }

    }

}
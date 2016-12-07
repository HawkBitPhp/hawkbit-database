<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 07.12.2016
 * Time: 07:11
 */

namespace Hawkbit\Storage;


class MapperLocator
{

    private $entityMapperMap = [];
    private $mapperMap = [];

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var Hydrator|null
     */
    private $hydrator;

    /**
     * MapperLocator constructor.
     * @param Connection $connection
     * @param Hydrator|null $hydrator
     */
    public function __construct(Connection $connection, Hydrator $hydrator = null)
    {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Hydrator|null
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @param Mapper|string $mapper
     * @param Hydrator|null $hydrator
     */
    public function register($mapper, Hydrator $hydrator = null)
    {
        if($this->has($mapper)){

        }
        $hydrator = null === $hydrator ? $this->getHydrator() : $hydrator;

        if(!is_object($mapper) && is_string($mapper)){
            if(!class_exists($mapper)){
                throw new \InvalidArgumentException('Unable to load mapper!');
            }

            if(!$this->isValid($mapper)){
                throw new \InvalidArgumentException('Invalid mapper class! Expect an instance of ' . Mapper::class);
            }

            $mapper = new $mapper($this->getConnection(), $hydrator);
        }

        if(!$this->isValid($mapper)){
            throw new \InvalidArgumentException('Invalid mapper object! Expect an instance of ' . Mapper::class);
        }

        $mapperClass = get_class($mapper);

        $this->entityMapperMap[$mapper->getEntityClass()] = $mapperClass;
        $this->mapperMap[$mapperClass] = $mapper;

    }

    /**
     * @param $entityOrMapper
     * @return bool
     */
    public function has($entityOrMapper){

        // try to find mapper by entity
        if($this->hasEntity($entityOrMapper)){
            $entityOrMapper = $this->entityMapperMap[$entityOrMapper];
        }

        return $this->hasMapper($entityOrMapper);
    }

    /**
     * @param $entityOrMapper
     * @return Mapper
     */
    public function locate($entityOrMapper){
        $entityOrMapper = $this->normalizeType($entityOrMapper);

        // try to find mapper by entity
        if($this->hasEntity($entityOrMapper)){
            $entityOrMapper = $this->entityMapperMap[$entityOrMapper];
        }

        if(false === $this->hasMapper($entityOrMapper)){
            throw new \InvalidArgumentException('Unable to locate mapper for ' . $entityOrMapper);
        }

        return $this->mapperMap[$entityOrMapper];
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasEntity($entity)
    {
        $entity = $this->normalizeType($entity);
        return isset($this->entityMapperMap[$entity]);
    }

    /**
     * @param $mapper
     * @return bool
     */
    public function hasMapper($mapper)
    {
        $mapper = $this->normalizeType($mapper);
        return isset($this->mapperMap[$mapper]);
    }

    /**
     * @param $classOrObject
     * @return string
     */
    private function normalizeType($classOrObject)
    {
        if (is_object($classOrObject)) {
            $classOrObject = get_class($classOrObject);
        }

        if (!is_string($classOrObject)) {
            throw new \InvalidArgumentException('Invalid data type ' . gettype($classOrObject));
        }
        return $classOrObject;
    }

    /**
     * @param $mapper
     * @return string
     */
    private function isValid($mapper)
    {
        if(is_object($mapper)){
            $mapper = get_class($mapper);
        }
        if(!is_string($mapper)){
            return false;
        }
        return '\\' . ltrim($mapper, '\\') instanceof Mapper;
    }

}
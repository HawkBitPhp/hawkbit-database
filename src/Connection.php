<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 14:04
 */

namespace Hawkbit\Storage;

use Doctrine\DBAL\Driver;


/**
 * The connection wrapper provides connection-aware mapper and query based on current connection
 *
 *
 * @package Blast\Orm
 */
final class Connection extends \Doctrine\DBAL\Connection
{

    /**
     * Table name prefix for connections
     * @var null|string
     */
    private $prefix = null;

    /**
     * @var MapperLocator
     */
    private $mapperLocator;

    /**
     * @var EntityStates
     */
    private $objectGraph;

    /**
     * @var IdentityMap[]
     */
    private $identityMap;

    /**
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param null|string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param $entityOrMapper
     * @return Mapper
     */
    public function loadMapper($entityOrMapper){

        return $this->getMapperLocator()->locate($entityOrMapper);
    }

    /**
     * @return MapperLocator
     */
    public function getMapperLocator()
    {
        if(null === $this->mapperLocator){
            $this->mapperLocator = new MapperLocator($this);
        }
        return $this->mapperLocator;
    }


    /**
     * @param $classOrObject
     * @return IdentityMap
     */
    public function loadIdentityMap($classOrObject)
    {
        if (is_object($classOrObject)) {
            $classOrObject = get_class($classOrObject);
        }

        if (!is_string($classOrObject)) {
            throw new \InvalidArgumentException('Invalid data type ' . gettype($classOrObject));
        }
        if(!isset($this->identityMap[$classOrObject])){
            $this->identityMap[$classOrObject] = new IdentityMap();
        }

        return $this->identityMap[$classOrObject];
    }

    /**
     * @return UnitOfWork
     */
    public function createUnitOfWork(){
        return new UnitOfWork($this);
    }

    /**
     * @return EntityStates
     */
    public function getObjectGraph(){
        if(null === $this->objectGraph){
            $this->objectGraph = new EntityStates($this);
        }
        return $this->objectGraph;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 14:04
 */

namespace Hawkbit\Database;

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
     * @return UnitOfWork
     */
    public function createUnitOfWork(){
        return new UnitOfWork($this);
    }

    /**
     * @param $table
     * @param null $alias
     * @return Gateway
     */
    public function createGateway($table, $alias = null){
        return new Gateway($this, $table, $alias);
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
     * @param $entityOrMapper
     * @return Mapper|AbstractMapper
     */
    public function loadMapper($entityOrMapper){

        return $this->getMapperLocator()->locate($entityOrMapper);
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

}
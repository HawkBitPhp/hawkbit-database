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
     * @var ObjectGraph
     */
    private $objectGraph;

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
     * @return UnitOfWork
     */
    public function createUnitOfWork(){
        return new UnitOfWork($this);
    }

    /**
     * @return ObjectGraph
     */
    public function getObjectGraph(){
        if(null === $this->objectGraph){
            $this->objectGraph = new ObjectGraph($this);
        }
        return $this->objectGraph;
    }

    /**
     * @return array
     */
    public function getIdentityStateGraph(){
        $mappers = $this->getMapperLocator()->getMapperMap();
        $objectGraph = $this->getObjectGraph();
        $graph = [];

        foreach ($mappers as $mapper){
            $identities = $mapper->getIdentityMap()->toArray();

            foreach ($identities as $id => $object){
                $identities[$id] = $objectGraph->getState($object);
            }

            $graph[$mapper->getEntityClass()] = $identities;
        }

        return $graph;
    }

}
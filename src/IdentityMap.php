<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 07.12.2016
 * Time: 07:35
 */

namespace Hawkbit\Storage;


use ArrayObject;
use OutOfBoundsException;
use SplObjectStorage;

class IdentityMap
{
    /**
     * @var ArrayObject
     */
    protected $idToObject;

    /**
     * @var SplObjectStorage
     */
    protected $objectToId;

    public function __construct()
    {
        $this->objectToId = new SplObjectStorage();
        $this->idToObject = new ArrayObject();
    }
    /**
     * @param integer $id
     * @param mixed $object
     */
    public function set($id, $object)
    {
        $this->idToObject[$id]     = $object;
        $this->objectToId[$object] = $id;
    }
    /**
     * @param mixed $object
     * @throws OutOfBoundsException
     * @return integer
     */
    public function getId($object)
    {
        if (false === $this->hasObject($object)) {
            throw new OutOfBoundsException();
        }

        /** @var integer $id */
        $id = $this->objectToId[$object];
        return $id;
    }
    /**
     * @param integer $id
     * @return boolean
     */
    public function hasId($id)
    {
        return isset($this->idToObject[$id]);
    }
    /**
     * @param mixed $object
     * @return boolean
     */
    public function hasObject($object)
    {
        return isset($this->objectToId[$object]);
    }
    /**
     * @param string|int $id
     * @throws OutOfBoundsException
     * @return object
     */
    public function getObject($id)
    {
        if (false === $this->hasId($id)) {
            throw new OutOfBoundsException();
        }
        return $this->idToObject[$id];
    }

    /**
     * @param $object
     */
    public function removeObject($object){
        if($this->hasObject($object)){
            $id = $this->getId($object);
            unset($this->objectToId[$object]);
            unset($this->idToObject[$id]);
        }
    }

    /**
     * @param $id
     */
    public function removeId($id){
        if($this->hasId($id)){
            $object = $this->getObject($id);
            unset($this->objectToId[$object]);
            unset($this->idToObject[$id]);
        }
    }
}
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

final class IdentityMap
{

    const ADDED = 'added';
    const MODIFIED = 'modified';
    const REMOVED = 'removed';
    const UNHANDLED = 'unhandled';

    /**
     * @var ArrayObject
     */
    protected $idToObject;

    /**
     * @var SplObjectStorage
     */
    protected $objectToId;

    /**
     * @var ArrayObject
     */
    private $removed;

    public function __construct()
    {
        $this->objectToId = new SplObjectStorage();
        $this->idToObject = new ArrayObject();
        $this->removed = new ArrayObject();
        $this->objectStorage = new \SplObjectStorage();
    }

    /**
     * @param $object
     */
    private function add($object)
    {
        $this->objectStorage[$object] = self::ADDED;
    }

    /**
     * @param $object
     */
    private function modify($object)
    {
        $this->objectStorage[$object] = self::MODIFIED;
    }

    /**
     * @param $object
     */
    private function delete($object)
    {
        $this->objectStorage[$object] = self::REMOVED;
    }

    /**
     * @param $object
     * @return object|string
     */
    public function getState($object)
    {
        if (isset($this->objectStorage[$object])) {
            return $this->objectStorage[$object];
        }

        return self::UNHANDLED;
    }

    /**
     * @param integer $id
     * @param mixed $object
     */
    public function set($id, $object)
    {
        if ($this->hasObject($object)) {
            $this->modify($object);
        }else{
            $this->add($object);
        }

        // remove old id's
        if($this->hasObject($object)){
            if($this->getId($object) !== $id){
                $this->removeId($id);
            }
        }

        $this->idToObject[$id] = $object;
        $this->objectToId[$object] = $id;
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
     * Remove link from object to id
     * @param $object
     */
    private function removeObject($object)
    {
        if ($this->hasObject($object)) {
            unset($this->objectToId[$object]);
        }
    }

    /**
     * Remove link from id to object
     * @param $id
     */
    private function removeId($id)
    {
        if ($this->hasId($id)) {
            unset($this->idToObject[$id]);
        }
    }

    /**
     * remove object by id
     * @param $id
     * @param $object
     */
    public function remove($id, $object){
        $this->removeId($id);
        $this->removeObject($object);
        $this->removed[$id] = $object;

        //update object state
        $this->delete($object);
    }

    /**
     * @return array
     */
    public function getModified()
    {
        return $this->idToObject->getArrayCopy();
    }

    /**
     * @return array
     */
    public function getRemoved()
    {
        return $this->removed->getArrayCopy();
    }

    /**
     * @return array
     */
    public function getGraph()
    {
        $graph = [];

        $modified = $this->getModified();
        $removed = $this->getRemoved();
        $identities = array_replace($modified, $removed);

        foreach ($identities as $id => $object) {
            $graph[get_class($object)][$id] = $this->getState($object);
        }

        return $graph;
    }
}
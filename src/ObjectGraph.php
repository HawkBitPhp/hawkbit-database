<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 07.12.2016
 * Time: 17:17
 */

namespace Hawkbit\Storage;

class ObjectGraph
{

    const ADDED = 'added';
    const MODIFIED = 'modified';
    const REMOVED = 'removed';
    const UNHANDLED = 'unhandled';

    /**
     * ObjectGraph constructor.
     */
    public function __construct()
    {
        $this->objectStorage = new \SplObjectStorage();
    }

    /**
     * @param $object
     */
    public function add($object){
        $this->objectStorage[$object] = self::ADDED;
    }

    /**
     * @param $object
     */
    public function modify($object){
        $this->objectStorage[$object] = self::MODIFIED;
    }

    /**
     * @param $object
     */
    public function remove($object){
        $this->objectStorage[$object] = self::REMOVED;
    }

    /**
     * @param $object
     * @return object|string
     */
    public function getState($object){
        if(isset($this->objectStorage[$object])){
            return $this->objectStorage[$object];
        }
        
        return self::UNHANDLED;
    }

    public function toArray(){
        $copy = [];

        foreach ($this->objectStorage as $object => $state){
            $copy[get_class($object)] = $state;
        }

        return $this->objectStorage->getInfo();
    }

}
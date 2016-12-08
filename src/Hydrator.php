<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 13:44
 */

namespace Hawkbit\Database;


use Hawkbit\Database\Support\MapperStore;
use Hawkbit\Database\Support\ReflectionStore;

/**
 * Class Hydrator
 *
 * Hydrate or extract from object properties
 *
 * @package Hawkbit\Database
 */
class Hydrator
{
    /**
     * Map key-value array
     * @param $array
     * @param null $object
     * @return object
     */
    public function hydrate($array, $object){
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();

        foreach ($properties as $property){
            if(!$property->isPublic()){
                $property->setAccessible(true);
            }

            $name = $property->getName();

            if(!isset($array[$name])){
                continue;
            }

            $property->setValue($object, $array[$name]);
        }

        return $object;
    }

    /**
     * Extract object to key value
     * @param $object
     * @return array
     */
    public function extract($object){
        $array = [];
        $reflection = new \ReflectionObject($object);
        $properties = $reflection->getProperties();

        foreach ($properties as $property){
            if(!$property->isPublic()){
                $property->setAccessible(true);
            }

            $array[$property->getName()] = $property->getValue($object);
        }

        return $array;
    }

}
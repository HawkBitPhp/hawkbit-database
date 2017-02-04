<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:07
 */

namespace Hawkbit\Database\Tests\Stubs;


class JsonEntity
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name = '';
    /**
     * @var string
     */
    private $namespace = '';
    /**
     * @var array
     */
    private $data = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return JsonEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return JsonEntity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return JsonEntity
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return JsonEntity
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:07
 */

namespace Hawkbit\Database\Tests\Stubs;


class UserEntity
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return UserEntity
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

}
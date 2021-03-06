<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:07
 */

namespace Hawkbit\Database\Tests\Stubs;


class PostEntity
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PostEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return PostEntity
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

}
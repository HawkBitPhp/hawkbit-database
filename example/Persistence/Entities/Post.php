<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 05.12.2016
 * Time: 10:07
 */

namespace Application\Persistence\Entities;


class Post
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title = '';

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
     * @return Post
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

}
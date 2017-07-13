<?php

namespace InstagramScraper\Model;


class Tag
{
    /**
     * @var int
     */
    private $mediaCount;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $id;

    /**
     * @return int
     */
    public function getMediaCount()
    {
        return $this->mediaCount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $tagArray
     *
     * @return Tag
     */
    public static function fromSearchPage($tagArray)
    {
        $instance = new self();
        $instance->mediaCount = $tagArray['media_count'];
        $instance->name = $tagArray['name'];
        $instance->id = $tagArray['id'];
        return $instance;
    }
}
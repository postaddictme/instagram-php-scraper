<?php

namespace InstagramScraper\Model;


class Tag
{
    /**
     * @var
     */
    private $mediaCount;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $id;

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
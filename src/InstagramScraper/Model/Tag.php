<?php

namespace InstagramScraper\Model;


class Tag
{
    public $mediaCount;
    public $name;
    public $id;

    function __construct()
    {
    }

    public static function fromSearchPage($tagArray)
    {
        $instance = new self();
        $instance->mediaCount = $tagArray['media_count'];
        $instance->name = $tagArray['name'];
        $instance->id = $tagArray['id'];
        return $instance;
    }
}
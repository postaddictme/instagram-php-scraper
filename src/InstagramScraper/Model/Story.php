<?php

namespace InstagramScraper\Model;

/**
 * Class Story
 * @package InstagramScraper\Model
 */
class Story extends AbstractModel
{
    /** @var  Account */
    protected $owner;

    /** @var  Media[] */
    protected $stories;

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function addStory($story)
    {
        $this->stories[] = $story;
    }

    public function setStories($stories)
    {
        $this->stories = $stories;
    }

    public function getStories()
    {
        return $this->stories;
    }
}
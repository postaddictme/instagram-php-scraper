<?php

namespace InstagramScraper\Model;

/**
 * Class UserStories
 * @package InstagramScraper\Model
 */
class UserStories extends AbstractModel
{
    /** @var  Account */
    protected $owner;

    /** @var  Story[] */
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
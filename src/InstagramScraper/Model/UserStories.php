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

    /** @var  expiringAT */
    protected $expiringAt=0;

    /** @var  lastMutatedAt */
    protected $lastMutatedAt=0;

    public function getExpiringAt()
    {
        return $this->expiringAt;
    }

    public function getLastMutatedAt()
    {
        return $this->lastMutatedAt;
    }

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

    /**
     * @param $value
     * @param $prop
     */
    protected function initPropertiesCustom($value, $prop, $arr)
    {
        switch ($prop) {
          case 'expiring_at':
              $this->expiringAt = (int)$value;
              break;
          case 'latest_reel_media':
              $this->lastMutatedAt = (int)$value;
              break;
        }
    }
}

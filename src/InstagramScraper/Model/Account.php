<?php

namespace InstagramScraper\Model;

/**
 * Class Account
 * @package InstagramScraper\Model
 */
class Account extends AbstractModel
{
    /**
     * User id
     * @var string
     */
    protected $id = 0;

    /**
     * Username
     * @var string
     */
    protected $username = '';

    /**
     * Full name
     * @var string
     */
    protected $fullName = '';

    /**
     * Profile picture url
     * @var string
     */
    protected $profilePicUrl = '';

    /**
     * Profile picture url HD
     * @var string
     */
    protected $profilePicUrlHd = '';

    /**
     * Information filled by user
     * @var string
     */
    protected $biography = '';

    /**
     * Url provided by user in profile
     * @var string
     */
    protected $externalUrl = '';

    /**
     * Number of subscriptions
     * @var integer
     */
    protected $followsCount = 0;

    /**
     * Number of followers
     * @var integer
     */
    protected $followedByCount = 0;

    /**
     * Number of medias published by user
     * @var integer
     */
    protected $mediaCount = 0;

    /**
     * true if account is private
     * @var boolean
     */
    protected $isPrivate = false;

    /**
     * true if verified by Instagram as celebrity
     * @var boolean
     */
    protected $isVerified = false;

    /**
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getId()
    {
        if (PHP_INT_SIZE > 4) {
            $this->id = (int)$this->id;
        }

        return $this->id;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getProfilePicUrl()
    {
        return $this->profilePicUrl;
    }

    /**
     * @return string
     */
    public function getProfilePicUrlHd()
    {
        $toReturn = $this->profilePicUrl;

        if ($this->profilePicUrlHd !== '') {
            $toReturn = $this->profilePicUrlHd;
        }

        return $toReturn;
    }

    /**
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @return string
     */
    public function getExternalUrl()
    {
        return $this->externalUrl;
    }

    /**
     * @return int
     */
    public function getFollowsCount()
    {
        return $this->followsCount;
    }

    /**
     * @return int
     */
    public function getFollowedByCount()
    {
        return $this->followedByCount;
    }

    /**
     * @return int
     */
    public function getMediaCount()
    {
        return $this->mediaCount;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->isVerified;
    }

    /**
     * @param $value
     * @param $prop
     * @param $array
     */
    protected function initPropertiesCustom($value, $prop, $array)
    {
        switch ($prop) {
            case 'id':
            case 'pk':
                $this->id = $value;
                break;
            case 'username':
                $this->username = $value;
                break;
            case 'full_name':
                $this->fullName = $value;
                break;
            case 'profile_pic_url':
                $this->profilePicUrl = $value;
                break;
            case 'profile_pic_url_hd':
                $this->profilePicUrlHd = $value;
                break;
            case 'biography':
                $this->biography = $value;
                break;
            case 'external_url':
                $this->externalUrl = $value;
                break;
            case 'edge_follow':
                $this->followsCount = !empty($array[$prop]['count']) ? (int)$array[$prop]['count'] : 0;
                break;
            case 'edge_followed_by':
                $this->followedByCount = !empty($array[$prop]['count']) ? (int)$array[$prop]['count'] : 0;
                break;
            case 'edge_owner_to_timeline_media':
                $this->mediaCount = !empty($array[$prop]['count']) ? $array[$prop]['count'] : 0;
                break;
            case 'is_private':
                $this->isPrivate = (bool)$value;
                break;
            case 'is_verified':
                $this->isVerified = (bool)$value;
                break;
        }
    }
}

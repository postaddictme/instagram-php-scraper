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
     * Profile picture id
     * @var string
     */
    protected $profilePicId = '';

    /**
     * true if account has anonymous profile picture
     * @var boolean
     */
    protected $hasAnonymousProfilePicture = false;

    /**
     * Mutual followers or number of followers
     * @var string
     */
    protected $byline = '';

    /**
     * Following or followed by
     * @var string
     */
    protected $socialContext = '';

    /**
     * Following or followed by
     * @var string
     */
    protected $searchSocialContext = '';

    /**
     * Number of mutual followers
     * @var integer
     */
    protected $mutualFollowersCount = 0;

    /**
     * Latest reel media timestamp
     * @var epoch
     */
    protected $latestReelMedia = '';

    /**
     * true if following user
     * @var boolean
     */
    protected $following = false;

    /**
     * true if follow requested
     * @var boolean
     */
    protected $outgoingRequest = false;

    /**
     * Number of unseen medias
     * @var int
     */
    protected $unseenMedias = 0;

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
        return (int)$this->id;
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
    public function getProfilePicId()
    {
        return $this->profilePicId;
    }

    /**
     * @return bool
     */
    public function hasAnonymousProfilePicture()
    {
        return $this->hasAnonymousProfilePicture;
    }


    /**
     * @return string
     */
    public function getByline()
    {
        return $this->byline;
    }

    /**
     * @return string
     */
    public function getSocialContext()
    {
        return $this->socialContext;
    }

    /**
     * @return string
     */
    public function getSearchSocialContext()
    {
        return $this->searchSocialContext;
    }

    /**
     * @return int
     */
    public function getMutualFollowersCount()
    {
        return $this->mutualFollowersCount;
    }

    /**
     * @return int
     */
    public function getLatestReelMedia()
    {
        return $this->latestReelMedia;
    }

    /**
     * @return bool
     */
    public function following()
    {
        return $this->following;
    }

    /**
     * @return bool
     */
    public function outgoingRequest()
    {
        return $this->outgoingRequest;
    }

    /**
     * @return int
     */
    public function getUnseenMedias()
    {
        return $this->unseenMedias;
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
            case 'pk':
                $this->id = (int)$value;
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
            case 'profile_pic_id':
                $this->profilePicId = $value;
                break;
            case 'has_anonymous_profile_picture':
                $this->hasAnonymousProfilePicture = (bool)$value;
                break;
            case 'byline':
                $this->byline = $value;
                break;
            case 'social_context':
                $this->socialContext = $value;
                break;
            case 'search_social_context':
                $this->searchSocialContext = $value;
                break;
            case 'mutual_followers_count':
                $this->mutualFollowersCount = $value;
                break;
            case 'latest_reel_media':
                $this->latestReelMedia = $value;
                break;
            case 'following':
                $this->following = $value;
                break;
            case 'outgoing_request':
                $this->outgoingRequest = $value;
                break;
            case 'unseen_count':
                $this->unseenMedias = $value;
                break;
            case 'biography':
                $this->biography = $value;
                break;
            case 'external_url':
                $this->externalUrl = $value;
                break;
            case 'follows':
                $this->followsCount = !empty($array[$prop]['count']) ? (int)$array[$prop]['count'] : 0;
                break;
            case 'follower_count':
                $this->followedByCount = $value;
                break;
            case 'media':
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

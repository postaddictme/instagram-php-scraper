<?php

namespace InstagramScraper\Model;

class Account
{
    /**
     * User id
     * @var string
     */
    private $id = '';

    /**
     * Username
     * @var string
     */
    private $username = '';

    /**
     * Full name
     * @var string
     */
    private $fullName = '';

    /**
     * Profile picture url
     * @var string
     */
    private $profilePicUrl = '';

    /**
     * Information filled by user
     * @var string
     */
    private $biography = '';

    /**
     * Url provided by user in profile
     * @var string
     */
    private $externalUrl = '';

    /**
     * Number of subscriptions
     * @var integer
     */
    private $followsCount = 0;

    /**
     * Number of followers
     * @var integer
     */
    private $followedByCount = 0;

    /**
     * Number of medias published by user
     * @var integer
     */
    private $mediaCount = 0;

    /**
     * true if account is private
     * @var boolean
     */
    private $isPrivate = false;

    /**
     * true if verified by Instagram as celebrity
     * @var boolean
     */
    private $isVerified = false;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getId()
    {
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
     * @param array $userArray
     *
     * @return Account
     */
    public static function fromComment($userArray)
    {
        $instance = new self();
        $instance->id = $userArray['id'];
        $instance->profilePicUrl = $userArray['profile_pic_url'];
        $instance->username = $userArray['username'];
        return $instance;
    }

    /**
     * @param array $userArray
     *
     * @return Account
     */
    public static function fromAccountPage($userArray)
    {
        $instance = new self();
        $instance->username = $userArray['username'];
        $instance->followsCount = $userArray['follows']['count'];
        $instance->followedByCount = $userArray['followed_by']['count'];
        $instance->profilePicUrl = $userArray['profile_pic_url'];
        $instance->id = $userArray['id'];
        $instance->biography = $userArray['biography'];
        $instance->fullName = $userArray['full_name'];
        $instance->mediaCount = $userArray['media']['count'];
        $instance->isPrivate = $userArray['is_private'];
        $instance->externalUrl = $userArray['external_url'];
        $instance->isVerified = $userArray['is_verified'];
        return $instance;
    }

    /**
     * @param array $userArray
     *
     * @return Account
     */
    public static function fromMediaPage($userArray)
    {
        $instance = new self();
        $instance->id = $userArray['id'];
        if (isset($userArray['profile_pic_url'])) {
            $instance->profilePicUrl = $userArray['profile_pic_url'];
        }
        if (isset($userArray['profile_picture'])) {
            $instance->profilePicUrl = $userArray['profile_picture'];
        }
        $instance->username = $userArray['username'];
        $instance->fullName = $userArray['full_name'];
        if (isset($userArray['is_private'])) {
            $instance->isPrivate = $userArray['is_private'];
        }
        return $instance;
    }
    /**
     * @param array $userArray
     *
     * @return Account
     */
    public static function fromSearchPage($userArray)
    {
        $instance = new self();
        $instance->username = $userArray['username'];
        $instance->profilePicUrl = $userArray['profile_pic_url'];
        $instance->id = $userArray['pk'];
        $instance->fullName = $userArray['full_name'];
        $instance->isPrivate = $userArray['is_private'];
        $instance->isVerified = $userArray['is_verified'];
        $instance->followedByCount = $userArray['follower_count'];
        return $instance;
    }
}
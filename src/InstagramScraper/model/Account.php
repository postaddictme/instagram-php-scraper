<?php

namespace InstagramScraper;

class Account
{
    /**
     * User id
     * @var string
     */
    public $id;

    /**
     * Username
     * @var string
     */
    public $username;

    /**
     * Full name
     * @var string
     */
    public $fullName;

    /**
     * Profile picture url
     * @var string
     */
    public $profilePicUrl;

    /**
     * Information filled by user
     * @var string
     */
    public $biography;

    /**
     * Url provided by user in profile
     * @var string
     */
    public $externalUrl;

    /**
     * Number of subscriptions
     * @var integer
     */
    public $followsCount;

    /**
     * Number of followers
     * @var integer
     */
    public $followedByCount;

    /**
     * Number of medias published by user
     * @var integer
     */
    public $mediaCount;

    /**
     * true if account is private
     * @var boolean
     */
    public $isPrivate;

    /**
     * true if verified by Instagram as celebrity
     * @var boolean
     */
    public $isVerified;

    function __construct()
    {
    }

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

    public static function fromMediaPage($userArray)
    {
        $instance = new self();
        $instance->username = $userArray['username'];
        $instance->profilePicUrl = $userArray['profile_pic_url'];
        $instance->id = $userArray['id'];
        $instance->fullName = $userArray['full_name'];
        $instance->isPrivate = $userArray['is_private'];
        return $instance;
    }
}
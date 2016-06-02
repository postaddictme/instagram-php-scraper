<?php

namespace InstagramScraper;

class Account
{
    public $id;
    public $username;
    public $fullName;
    public $profilePicUrl;
    public $biography;
    public $externalUrl;
    public $followsCount;
    public $followedByCount;
    public $mediaCount;
    public $isPrivate;
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
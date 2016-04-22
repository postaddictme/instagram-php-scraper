<?php

namespace InstagramScraper;

class Account
{
    public $username;
    public $followsCount;
    public $followedByCount;
    public $profilePicUrl;
    public $id;
    public $biography;
    public $fullName;
    public $mediaCount;
    public $isPrivate;
    public $externalUrl;

    function __construct($userArray)
    {
        $this->username = $userArray['username'];
        $this->followsCount = $userArray['follows']['count'];
        $this->followedByCount = $userArray['followed_by']['count'];
        $this->profilePicUrl = $userArray['profile_pic_url'];
        $this->id = $userArray['id'];
        $this->biography = $userArray['biography'];
        $this->fullName = $userArray['full_name'];
        $this->mediaCount = $userArray['media']['count'];
        $this->isPrivate = $userArray['is_private'];
        $this->externalUrl = $userArray['external_url'];
    }
}
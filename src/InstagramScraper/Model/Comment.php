<?php

namespace InstagramScraper\Model;


class Comment
{
    public $text;
    public $createdAt;
    public $id;

    public $user;

    function __construct()
    {
    }

    public static function fromApi($commentArray)
    {
        $instance = new self();
        $instance->text = $commentArray['text'];
        $instance->createdAt = $commentArray['created_at'];
        $instance->id = $commentArray['id'];
        $instance->user = Account::fromAccountPage($commentArray['user']);
        return $instance;
    }

}
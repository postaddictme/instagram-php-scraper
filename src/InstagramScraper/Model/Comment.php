<?php

namespace InstagramScraper\Model;


class Comment
{
    /**
     * @var
     */
    private $id;

    /**
     * @var
     */
    private $text;

    /**
     * @var
     */
    private $createdAt;

    /**
     * @var
     */
    private $owner;

    /**
     * @param $commentArray
     *
     * @return Comment
     */
    public static function fromApi($commentArray)
    {
        $instance = new self();
        $instance->id = $commentArray['id'];
        $instance->createdAt = $commentArray['created_at'];
        $instance->text = $commentArray['text'];
        $instance->owner = Account::fromComment($commentArray['owner']);
        return $instance;
    }

}
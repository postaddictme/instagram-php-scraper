<?php

namespace InstagramScraper\Model;


class Comment extends AbstractModel
{
    /**
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $text;

    /**
     * @var
     */
    protected $createdAt;

    /**
     * @var Account
     */
    protected $owner;

    /**
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * @param $value
     * @param $prop
     */
    protected function initPropertiesCustom($value, $prop)
    {
        switch ($prop) {
            case 'id':
                $this->id = $value;
                break;
            case 'created_at':
                $this->createdAt = $value;
                break;
            case 'text':
                $this->text = $value;
                break;
            case 'owner':
                $this->owner = Account::create($value);
                break;
        }
    }

}
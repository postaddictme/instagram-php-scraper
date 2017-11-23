<?php

namespace InstagramScraper\Model;


class Like extends AbstractModel
{
    /**
     * @var
     */
    protected $id;

    /**
     * @var Account
     */
    protected $username;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->username;
    }

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
            case 'username':
                $this->username = $value;
                break;
        }
    }

}
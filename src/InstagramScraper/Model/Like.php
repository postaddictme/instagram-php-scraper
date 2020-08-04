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
     * @var Fullname
     */
    protected $fullName;

    /**
     * @var Picture
     */
    protected $profilePicUrl;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->username;
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
            case 'full_name':
                $this->fullName = $value;
                break;
            case 'profile_pic_url':
                $this->profilePicUrl = $value;
                break;
        }
    }

}

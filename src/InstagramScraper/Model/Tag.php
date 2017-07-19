<?php

namespace InstagramScraper\Model;


class Tag extends AbstractModel
{
    /**
     * @var int
     */
    protected $mediaCount = 0;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $id;

    /**
     * @return int
     */
    public function getMediaCount()
    {
        return $this->mediaCount;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $value
     * @param $prop
     */
    protected function initPropertiesCustom($value, $prop)
    {
        switch ($prop) {
            case 'media_count':
                $this->mediaCount = $value;
                break;
            case 'name':
                $this->name = $value;
                break;
            case 'id':
                $this->id = $value;
                break;
        }
    }
}
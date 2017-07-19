<?php

namespace InstagramScraper\Model;


class Location extends AbstractModel
{
    /**
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $hasPublicPage;

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $slug;

    /**
     * @var
     */
    protected $lng;

    /**
     * @var
     */
    protected $lat;

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
            case 'has_public_page':
                $this->hasPublicPage = $value;
                break;
            case 'name':
                $this->name = $value;
                break;
            case 'slug':
                $this->slug = $value;
                break;
            case 'lat':
                $this->lat = $value;
                break;
            case 'lng':
                $this->lng = $value;
                break;
        }
    }
}
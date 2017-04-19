<?php

namespace InstagramScraper\Model;


class Location
{
    public $id;
    public $hasPublicPage;
    public $name;
    public $slug;

    public $lng;
    public $lat;

    function __construct()
    {
    }

    public static function makeLocation($locationArray)
    {
        $instance = new self();
        $instance->id = $locationArray['id'];
        $instance->hasPublicPage = $locationArray['has_public_page'];
        if (isset($locationArray['name'])) {
            $instance->name = $locationArray['name'];
        }
        if (isset($locationArray['slug'])) {
            $instance->slug = $locationArray['slug'];
        }
        if (isset($locationArray['lat'])) {
            $instance->lat = $locationArray['lat'];
        }
        if (isset($locationArray['lng'])) {
            $instance->lng = $locationArray['lng'];
        }
        return $instance;
    }
}
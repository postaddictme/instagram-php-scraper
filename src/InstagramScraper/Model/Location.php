<?php

namespace InstagramScraper\Model;


class Location
{
    public $id;
    public $name;
    public $lat;
    public $lng;

    function __construct()
    {
    }

    public static function makeLocation($locationArray)
    {
        $location = new Location();
        $location->id = $locationArray['id'];
        $location->name = $locationArray['name'];
        $location->lat = $locationArray['lat'];
        $location->lng = $locationArray['lng'];
        return $location;
    }
}
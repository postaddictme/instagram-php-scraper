<?php

namespace InstagramScraper\Model;


class Location
{
    /**
     * @var
     */
    private $id;

    /**
     * @var
     */
    private $hasPublicPage;

    /**
     * @var
     */
    private $name;

    /**
     * @var
     */
    private $slug;

    /**
     * @var
     */
    private $lng;

    /**
     * @var
     */
    private $lat;

    /**
     * @param array $locationArray
     *
     * @return Location
     */
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
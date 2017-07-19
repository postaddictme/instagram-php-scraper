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
     * @var array
     */
    protected static $initPropertiesMap = [
        'id'              => 'id',
        'has_public_page' => 'hasPublicPage',
        'name'            => '',
        'slug'            => 'slug',
        'lat'             => 'lat',
        'lng'             => 'lng',
    ];
}
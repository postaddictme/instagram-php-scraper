<?php

namespace InstagramScraper\Model;


class Location extends AbstractModel
{
    /**
     * @var array
     */
    protected static $initPropertiesMap = [
        'id'              => 'id',
        'has_public_page' => 'hasPublicPage',
        'name'            => 'name',
        'slug'            => 'slug',
        'lat'             => 'lat',
        'lng'             => 'lng',
        'profile_pic_url' => 'profilePicUrl',
        'modified'        => 'modified',
        'pk'              => 'pk',
        'short_name'      => 'short_name',
        'external_source'      => 'external_source',
        'address'      => 'address',
        'city'      => 'city',
        'has_viewer_saved'      => 'has_viewer_saved',
        'lng'             => 'lng',
        'lat'             => 'lat',
        'facebook_places_id'      => 'facebook_places_id',
    ];
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
    protected $profilePicUrl;
    /**
     * @var
     */
    protected $lat;
    /**
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * @var
     */
    protected $modified;

    /**
     * @var
     */
    protected $pk;

    /**
     * @var
     */
    protected $short_name;

    /**
     * @var
     */
    protected $facebook_places_id;


    /**
     * @var
     */
    protected $external_source;

    /**
     * @var
     */
    protected $address;

    /**
     * @var
     */
    protected $city;

    /**
     * @var
     */
    protected $has_viewer_saved;



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
    public function getHasPublicPage()
    {
        return $this->hasPublicPage;
    }
    /**
     * @return mixed
     */
    public function getProfilePicUrl()
    {
        return $this->profilePicUrl;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @return mixed
     */
    public function getPk()
    {
        return $this->pk;
    }


    /**
     * @return mixed
     */
    public function getShortNname()
    {
        return $this->short_name;
    }

    /**
     * @return mixed
     */
    public function getFacebookPlacesId()
    {
        return $this->facebook_places_id;
    }


    /**
     * @return mixed
     */
    public function externalSource()
    {
        return $this->external_source;
    }


    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function isHasViewerSaved()
    {
        return $this->has_viewer_saved;
    }

}

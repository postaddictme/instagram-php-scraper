<?php

/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 20/04/16
 * Time: 01:15
 */
class Media
{
    public $id;
    public $createdTime;
    public $type;
    public $link;
    public $imageLowResolutionUrl;
    public $imageThumbnailUrl;
    public $imageStandardResolutionUrl;
    public $caption;
    public $videoLowResolutionUrl;
    public $videoStandardResolutionUrl;
    public $videoLowBandwidthUrl;

    function __construct($mediaArray)
    {
        $this->id = $mediaArray['id'];
        $this->type = $mediaArray['type'];
        $this->createdTime = $mediaArray['created_time'];
        $this->link = $mediaArray['link'];
        $this->imageLowResolutionUrl = $mediaArray['images']['low_resolution']['url'];
        $this->imageThumbnailUrl = $mediaArray['images']['thumbnail']['url'];
        $this->imageStandardResolutionUrl = $mediaArray['images']['standard_resolution']['url'];
        $this->caption = $mediaArray['caption']['text'];

        if ($this->type === 'video') {
            $this->videoLowResolutionUrl = $mediaArray['videos']['low_resolution']['url'];
            $this->videoStandardResolutionUrl = $mediaArray['videos']['standard_resolution']['url'];
            $this->videoLowBandwidthUrl = $mediaArray['videos']['low_bandwidth']['url'];
        }
    }
}
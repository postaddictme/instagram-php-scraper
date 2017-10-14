<?php

namespace InstagramScraper\Model;

/**
 * Class CarouselMedia
 * @package InstagramScraper\Model
 */
class CarouselMedia
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $imageLowResolutionUrl;

    /**
     * @var string
     */
    private $imageThumbnailUrl;

    /**
     * @var string
     */
    private $imageStandardResolutionUrl;

    /**
     * @var string
     */
    private $imageHighResolutionUrl;

    /**
     * @var string
     */
    private $videoLowResolutionUrl;

    /**
     * @var string
     */
    private $videoStandardResolutionUrl;

    /**
     * @var string
     */
    private $videoLowBandwidthUrl;

    /**
     * @var
     */
    private $videoViews;

    /**
     * CarouselMedia constructor.
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageLowResolutionUrl()
    {
        return $this->imageLowResolutionUrl;
    }

    /**
     * @param mixed $imageLowResolutionUrl
     *
     * @return CarouselMedia
     */
    public function setImageLowResolutionUrl($imageLowResolutionUrl)
    {
        $this->imageLowResolutionUrl = $imageLowResolutionUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageThumbnailUrl()
    {
        return $this->imageThumbnailUrl;
    }

    /**
     * @param mixed $imageThumbnailUrl
     *
     * @return CarouselMedia
     */
    public function setImageThumbnailUrl($imageThumbnailUrl)
    {
        $this->imageThumbnailUrl = $imageThumbnailUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageStandardResolutionUrl()
    {
        return $this->imageStandardResolutionUrl;
    }

    /**
     * @param mixed $imageStandardResolutionUrl
     *
     * @return CarouselMedia
     */
    public function setImageStandardResolutionUrl($imageStandardResolutionUrl)
    {
        $this->imageStandardResolutionUrl = $imageStandardResolutionUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageHighResolutionUrl()
    {
        return $this->imageHighResolutionUrl;
    }

    /**
     * @param mixed $imageHighResolutionUrl
     *
     * @return CarouselMedia
     */
    public function setImageHighResolutionUrl($imageHighResolutionUrl)
    {
        $this->imageHighResolutionUrl = $imageHighResolutionUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoLowResolutionUrl()
    {
        return $this->videoLowResolutionUrl;
    }

    /**
     * @param mixed $videoLowResolutionUrl
     *
     * @return CarouselMedia
     */
    public function setVideoLowResolutionUrl($videoLowResolutionUrl)
    {
        $this->videoLowResolutionUrl = $videoLowResolutionUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoStandardResolutionUrl()
    {
        return $this->videoStandardResolutionUrl;
    }

    /**
     * @param mixed $videoStandardResolutionUrl
     *
     * @return CarouselMedia
     */
    public function setVideoStandardResolutionUrl($videoStandardResolutionUrl)
    {
        $this->videoStandardResolutionUrl = $videoStandardResolutionUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoLowBandwidthUrl()
    {
        return $this->videoLowBandwidthUrl;
    }

    /**
     * @param mixed $videoLowBandwidthUrl
     *
     * @return CarouselMedia
     */
    public function setVideoLowBandwidthUrl($videoLowBandwidthUrl)
    {
        $this->videoLowBandwidthUrl = $videoLowBandwidthUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoViews()
    {
        return $this->videoViews;
    }

    /**
     * @param mixed $videoViews
     *
     * @return CarouselMedia
     */
    public function setVideoViews($videoViews)
    {
        $this->videoViews = $videoViews;
        return $this;
    }

}

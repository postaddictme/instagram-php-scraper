<?php

namespace InstagramScraper\Model;

use InstagramScraper\Endpoints;

class Media
{
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_SIDECAR = 'sidecar';
    const TYPE_CAROUSEL = 'carousel';

    /**
     * @var string
     */
    private $id = '';

    /**
     * @var string
     */
    private $shortCode = '';

    /**
     * @var int
     */
    private $createdTime = 0;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var string
     */
    private $link = '';

    /**
     * @var string
     */
    private $imageLowResolutionUrl = '';

    /**
     * @var string
     */
    private $imageThumbnailUrl = '';

    /**
     * @var string
     */
    private $imageStandardResolutionUrl = '';

    /**
     * @var string
     */
    private $imageHighResolutionUrl = '';

    /**
     * @var array
     */
    private $carouselMedia = [];

    /**
     * @var string
     */
    private $caption = '';

    /**
     * @var bool
     */
    private $isCaptionEdited = false;

    /**
     * @var bool
     */
    private $isAd = false;

    /**
     * @var string
     */
    private $videoLowResolutionUrl = '';

    /**
     * @var string
     */
    private $videoStandardResolutionUrl = '';

    /**
     * @var string
     */
    private $videoLowBandwidthUrl = '';

    /**
     * @var int
     */
    private $videoViews = 0;

    /**
     * @var Account
     */
    private $owner;

    /**
     * @var int
     */
    private $ownerId = 0;

    /**
     * @var int
     */
    private $likesCount = 0;

    /**
     * @var
     */
    private $locationId;

    /**
     * @var string
     */
    private $locationName = '';

    /**
     * @var string
     */
    private $commentsCount = 0;

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
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * @return int
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getImageLowResolutionUrl()
    {
        return $this->imageLowResolutionUrl;
    }

    /**
     * @return string
     */
    public function getImageThumbnailUrl()
    {
        return $this->imageThumbnailUrl;
    }

    /**
     * @return string
     */
    public function getImageStandardResolutionUrl()
    {
        return $this->imageStandardResolutionUrl;
    }

    /**
     * @return string
     */
    public function getImageHighResolutionUrl()
    {
        return $this->imageHighResolutionUrl;
    }

    /**
     * @return array
     */
    public function getCarouselMedia()
    {
        return $this->carouselMedia;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return bool
     */
    public function isCaptionEdited()
    {
        return $this->isCaptionEdited;
    }

    /**
     * @return bool
     */
    public function isAd()
    {
        return $this->isAd;
    }

    /**
     * @return string
     */
    public function getVideoLowResolutionUrl()
    {
        return $this->videoLowResolutionUrl;
    }

    /**
     * @return string
     */
    public function getVideoStandardResolutionUrl()
    {
        return $this->videoStandardResolutionUrl;
    }

    /**
     * @return string
     */
    public function getVideoLowBandwidthUrl()
    {
        return $this->videoLowBandwidthUrl;
    }

    /**
     * @return int
     */
    public function getVideoViews()
    {
        return $this->videoViews;
    }

    /**
     * @return Account
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return int
     */
    public function getLikesCount()
    {
        return $this->likesCount;
    }

    /**
     * @return mixed
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * @return string
     */
    public function getLocationName()
    {
        return $this->locationName;
    }

    /**
     * @return string
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * @param array $mediaArray
     *
     * @return Media
     */
    public static function fromApi($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = $mediaArray['type'];
        $instance->createdTime = (int) $mediaArray['created_time'];
        $instance->shortCode = $mediaArray['code'];
        $instance->link = $mediaArray['link'];
        $instance->commentsCount = $mediaArray['comments']['count'];
        $instance->likesCount = $mediaArray['likes']['count'];
        $images = self::getImageUrls($mediaArray['images']['standard_resolution']['url']);
        $instance->imageLowResolutionUrl = $images['low'];
        $instance->imageThumbnailUrl = $images['thumbnail'];
        $instance->imageStandardResolutionUrl = $images['standard'];
        $instance->imageHighResolutionUrl = $images['high'];
        if (isset($mediaArray["carousel_media"])) {
            $instance->carouselMedia = [];
            foreach ($mediaArray["carousel_media"] as $carouselArray) {
                self::setCarouselMedia($mediaArray, $carouselArray, $instance);
            }
        }

        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption']['text'];
        }
        if ($instance->type === self::TYPE_VIDEO) {
            if (isset($mediaArray['video_views'])) {
                $instance->videoViews = $mediaArray['video_views'];
            }
            if (isset($mediaArray['videos'])) {
                $instance->videoLowResolutionUrl = $mediaArray['videos']['low_resolution']['url'];
                $instance->videoStandardResolutionUrl = $mediaArray['videos']['standard_resolution']['url'];
                $instance->videoLowBandwidthUrl = $mediaArray['videos']['low_bandwidth']['url'];
            }
        }
        if (isset($mediaArray['location']['id'])) {
            $instance->locationId = $mediaArray['location']['id'];
        }
        if (isset($mediaArray['location']['name'])) {
            $instance->locationName = $mediaArray['location']['name'];
        }
        $instance->owner = Account::create($mediaArray['user']);
        $instance->ownerId = $instance->getOwner()->getId();
        return $instance;
    }

    /**
     * @param array $mediaArray
     *
     * @return Media
     */
    public static function fromMediaPage($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = self::TYPE_IMAGE;
        if ($mediaArray['is_video']) {
            $instance->type = self::TYPE_VIDEO;
            $instance->videoStandardResolutionUrl = $mediaArray['video_url'];
            $instance->videoViews = $mediaArray['video_view_count'];
        }
        if (isset($mediaArray["carousel_media"])) {
            $instance->type = self::TYPE_CAROUSEL;
            $instance->carouselMedia = [];
            foreach ($mediaArray["carousel_media"] as $carouselArray) {
                $mediaArray = self::setCarouselMedia($mediaArray, $carouselArray, $instance);
            }
        }
        if (isset($mediaArray['caption_is_edited'])) {
            $instance->isCaptionEdited = $mediaArray['caption_is_edited'];
        }
        if (isset($mediaArray['is_ad'])) {
            $instance->isAd = $mediaArray['is_ad'];
        }
        $instance->createdTime = (int) $mediaArray['taken_at_timestamp'];
        $instance->shortCode = $mediaArray['shortcode'];
        $instance->link = Endpoints::getMediaPageLink($instance->shortCode);
        $instance->commentsCount = $mediaArray['edge_media_to_comment']['count'];
        $instance->likesCount = $mediaArray['edge_media_preview_like']['count'];
        $images = self::getImageUrls($mediaArray['display_url']);
        $instance->imageStandardResolutionUrl = $images['standard'];
        $instance->imageLowResolutionUrl = $images['low'];
        $instance->imageHighResolutionUrl = $images['high'];
        $instance->imageThumbnailUrl = $images['thumbnail'];
        if (isset($mediaArray['edge_media_to_caption']['edges'][0]['node']['text'])) {
            $instance->caption = $mediaArray['edge_media_to_caption']['edges'][0]['node']['text'];
        }
        if (isset($mediaArray['location']['id'])) {
            $instance->locationId = $mediaArray['location']['id'];
        }
        if (isset($mediaArray['location']['name'])) {
            $instance->locationName = $mediaArray['location']['name'];
        }
        $instance->owner = Account::create($mediaArray['owner']);
        return $instance;
    }

    /**
     * @param array $mediaArray
     *
     * @return Media
     */
    public static function fromTagPage($mediaArray)
    {
        $instance = new self();
        $instance->shortCode = $mediaArray['code'];
        $instance->link = Endpoints::getMediaPageLink($instance->shortCode);
        $instance->commentsCount = (int) $mediaArray['comments']['count'];
        $instance->likesCount = (int) $mediaArray['likes']['count'];
        $instance->ownerId = (int) $mediaArray['owner']['id'];
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption'];
        }
        $instance->createdTime = (int) $mediaArray['date'];
        $images = self::getImageUrls($mediaArray['display_src']);
        $instance->imageStandardResolutionUrl = $images['standard'];
        $instance->imageLowResolutionUrl = $images['low'];
        $instance->imageHighResolutionUrl = $images['high'];
        $instance->imageThumbnailUrl = $images['thumbnail'];
        $instance->type = self::TYPE_IMAGE;
        if ($mediaArray['is_video']) {
            $instance->type = self::TYPE_VIDEO;
            $instance->videoViews = $mediaArray['video_views'];
        }
        if (isset($mediaArray["carousel_media"])) {
            $instance->type = self::TYPE_CAROUSEL;
            $instance->carouselMedia = [];
            foreach ($mediaArray["carousel_media"] as $carouselArray) {
                $mediaArray = self::setCarouselMedia($mediaArray, $carouselArray, $instance);
            }
        }
        $instance->id = $mediaArray['id'];
        return $instance;
    }

    /**
     * @param string $code
     *
     * @return int
     */
    public static function getIdFromCode($code)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $id = 0;
        for ($i = 0; $i < strlen($code); $i++) {
            $c = $code[$i];
            $id = $id * 64 + strpos($alphabet, $c);
        }
        return $id;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public static function getLinkFromId($id)
    {
        $code = Media::getCodeFromId($id);
        return Endpoints::getMediaPageLink($code);
    }

    /**
     * @param string $id
     *
     * @return string
     */
    public static function getCodeFromId($id)
    {
        $parts = explode('_', $id);
        $id = $parts[0];
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        $code = '';
        while ($id > 0) {
            $remainder = $id % 64;
            $id = ($id - $remainder) / 64;
            $code = $alphabet{$remainder} . $code;
        };
        return $code;
    }
    /**
     * @param string $imageUrl
     *
     * @return array
     */
    private static function getImageUrls($imageUrl)
    {
        $parts = explode('/', parse_url($imageUrl)['path']);
        $imageName = $parts[sizeof($parts) - 1];
        $urls = [
            'thumbnail' => Endpoints::INSTAGRAM_CDN_URL . 't/s150x150/' . $imageName,
            'low'       => Endpoints::INSTAGRAM_CDN_URL . 't/s320x320/' . $imageName,
            'standard'  => Endpoints::INSTAGRAM_CDN_URL . 't/s640x640/' . $imageName,
            'high'      => Endpoints::INSTAGRAM_CDN_URL . 't/' . $imageName,
        ];
        return $urls;
    }

    /**
     * @param $mediaArray
     * @param $carouselArray
     * @param $instance
     *
     * @return mixed
     */
    private static function setCarouselMedia($mediaArray, $carouselArray, $instance)
    {
        $carouselMedia = new CarouselMedia();
        $carouselMedia->setType($carouselArray['type']);

        if (isset($carouselArray['images'])) {
            $carouselImages = self::getImageUrls($carouselArray['images']['standard_resolution']['url']);
            $carouselMedia->setImageLowResolutionUrl($carouselImages['low']);
            $carouselMedia->setImageThumbnailUrl($carouselImages['thumbnail']);
            $carouselMedia->setImageStandardResolutionUrl($carouselImages['standard']);
            $carouselMedia->setImageHighResolutionUrl($carouselImages['high']);
        }

        if ($carouselMedia->getType() === self::TYPE_VIDEO) {
            if (isset($mediaArray['video_views'])) {
                $carouselMedia->setVideoViews($carouselArray['video_views']);
            }
            if (isset($carouselArray['videos'])) {
                $carouselMedia->setVideoLowResolutionUrl($carouselArray['videos']['low_resolution']['url']);
                $carouselMedia->setVideoStandardResolutionUrl($carouselArray['videos']['standard_resolution']['url']);
                $carouselMedia->setVideoLowBandwidthUrl($carouselArray['videos']['low_bandwidth']['url']);
            }
        }
        array_push($instance->carouselMedia, $carouselMedia);
        return $mediaArray;
    }
}

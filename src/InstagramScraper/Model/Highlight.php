<?php

namespace InstagramScraper\Model;

/**
 * Class Highlight
 * @package InstagramScraper\Model
 */
class Highlight extends AbstractModel
{
    /**
     * @var string
     */
    protected $id = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $imageThumbnailUrl = '';

    /**
     * @var string
     */
    protected $imageCroppedThumbnailUrl = '';

    /**
     * @var Account
     */
    protected $owner;

    /**
     * @var string
     */
    protected $ownerId = '';

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
    public function getImageCroppedThumbnailUrl()
    {
        return $this->imageCroppedThumbnailUrl;
    }

    /**
     * @return Account
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /***
     * @param $value
     * @param $prop
     * @param $arr
     */
    protected function initPropertiesCustom($value, $prop, $arr)
    {
        switch ($prop) {
            case 'id':
                $this->id = $value;
                break;
            case 'title':
                $this->title = $value;
                break;
            case 'cover_media':
                $this->imageThumbnailUrl = $value['thumbnail_src'];
                break;
            case 'cover_media_cropped_thumbnail':
                $this->imageCroppedThumbnailUrl = $value['url'];
                break;
            case 'owner':
                $this->owner = Account::create($value);
                break;
        }
        if (!$this->ownerId && !is_null($this->owner)) {
            $this->ownerId = $this->getOwner()->getId();
        }
    }
}

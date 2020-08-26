<?php

namespace InstagramScraper\Model;

/**
 * Class Media
 * @package InstagramScraper\Model
 */
class ReelShare extends AbstractModel
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $mentionedId;

    /**
     * @var int
     */
    protected $ownerId;

    /**
     * @var ReelMedia
     */
    protected $media;

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getMentionedId()
    {
        return $this->mentionedId;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return ReelMedia
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param $value
     * @param $prop
     * @param $arr
     */
    protected function initPropertiesCustom($value, $prop, $arr)
    {
        switch ($prop) {
            case 'text':
                $this->text = $value;
                break;
            case 'type':
                $this->type = $value;
                break;
            case 'mentioned_user_id':
                $this->mentionedId = (int) $value;
                break;
            case 'reel_owner_id':
                $this->ownerId = (int) $value;
                break;
            case 'media':
                $this->media = ReelMedia::create($arr[$prop]);
                break;
        }
    }
}

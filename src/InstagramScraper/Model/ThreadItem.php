<?php

namespace InstagramScraper\Model;

/**
 * Class Media
 * @package InstagramScraper\Model
 */
class ThreadItem extends AbstractModel
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $time;

    /**
     * @var string
     */
    protected $userId;

    /**
     * @var ReelShare
     */
    protected $reelShare;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return ReelShare
     */
    public function getReelShare()
    {
        return $this->reelShare;
    }

    /**
     * @param $value
     * @param $prop
     * @param $arr
     */
    protected function initPropertiesCustom($value, $prop, $arr)
    {
        switch ($prop) {
            case 'item_id':
                $this->id = $value;
                break;
            case 'item_type':
                $this->type = $value;
                break;
            case 'text':
                if ($arr['item_type'] === 'text') {
                    $this->text = $value;
                }
                break;
            case 'reel_share':
                if ($arr['item_type'] === 'reel_share') {
                    $this->reelShare = ReelShare::create($arr[$prop]);
                }
                break;
            case 'timestamp':
                $this->time = (int) ($value / 1000000);
                break;
            case 'user_id':
                $this->userId = $value;
                break;
        }
    }
}

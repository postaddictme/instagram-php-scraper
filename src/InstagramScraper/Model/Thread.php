<?php

namespace InstagramScraper\Model;

/**
 * Class Media
 * @package InstagramScraper\Model
 */
class Thread extends AbstractModel
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $archived;

    /**
     * @var boolean
     */
    protected $hasNewer;

    /**
     * @var boolean
     */
    protected $hasOlder;

    /**
     * @var boolean
     */
    protected $isGroup;

    /**
     * @var boolean
     */
    protected $isPin;

    /**
     * @var boolean
     */
    protected $readState;

    /**
     * @var array
     */
    protected $items = [];

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @return boolean
     */
    public function hasNewer()
    {
        return $this->hasNewer;
    }

    /**
     * @return boolean
     */
    public function hasOlder()
    {
        return $this->hasOlder;
    }

    /**
     * @return boolean
     */
    public function isGroup()
    {
        return $this->isGroup;
    }

    /**
     * @return boolean
     */
    public function isPin()
    {
        return $this->isPin;
    }

    /**
     * @return boolean
     */
    public function getReadState()
    {
        return $this->readState;
    }

    /**
     * @return ThreadItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param $value
     * @param $prop
     * @param $arr
     */
    protected function initPropertiesCustom($value, $prop, $arr)
    {
        switch ($prop) {
            case 'thread_id':
                $this->id = $value;
                break;
            case 'thread_title':
                $this->title = $value;
                break;
            case 'thread_type':
                $this->type = $value;
                break;
            case 'archived':
                $this->archived = (bool)$value;
                break;
            case 'has_newer':
                $this->hasNewer = (bool)$value;
                break;
            case 'has_older':
                $this->hasOlder = (bool)$value;
                break;
            case 'is_group':
                $this->isGroup = (bool)$value;
                break;
            case 'is_pin':
                $this->isPin = (bool)$value;
                break;
            case 'read_state':
                $this->readState = (bool)$value;
                break;
            case 'items':
                foreach ($arr[$prop] as $item) {
                    $this->items[] = ThreadItem::create($item);
                }
                break;
        }
    }
}

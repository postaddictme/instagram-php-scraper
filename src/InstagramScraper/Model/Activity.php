<?php


namespace InstagramScraper\Model;


class Activity extends AbstractModel
{
    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var int
     */
    public $count;

    /**
     * @var ActivityElement[]
     */
    public $activityElements = [];

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return ActivityElement[]
     */
    public function getActivityElements()
    {
        return $this->activityElements;
    }

    /**
     * @param ActivityElement $activityElement
     */
    public function addActivityElement($activityElement)
    {
        $this->activityElements[] = $activityElement;
    }

    /**
     * @param $value
     * @param $prop
     * @param $props
     */
    protected function initPropertiesCustom($value, $prop, $props)
    {
        switch ($prop) {
            case 'timestamp':
                $this->timestamp = $value;
                break;
            case 'edge_web_activity_feed':
                $this->count = $value['count'];
                if ($this->count) {
                    foreach ($value['edges'] as $edge) {
                        $this->addActivityElement(ActivityElement::create($edge['node']));
                    }
                }
                break;
        }
    }

}
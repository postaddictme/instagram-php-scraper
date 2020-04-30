<?php


namespace InstagramScraper\Model;


class ActivityElement extends AbstractModel
{
    const TYPE_LIKE = 1; // GraphLikeAggregatedStory
    const TYPE_SUBSRIBE = 3; // GraphFollowAggregatedStory
    const TYPE_MENTIONED = 5; // GraphMentionStory
    const TYPE_TAGGED = 12; // GraphUserTaggedStory

    /**
     * @var string
     */
    public $id;

    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $typeName;

    private $typeNames = [
        'GraphLikeAggregatedStory' => 'like',
        'GraphFollowAggregatedStory' => 'followed',
        'GraphMentionStory' => 'mentioned',
        'GraphUserTaggedStory' => 'tagged',
    ];

    /**
     * @var int
     */
    public $timestamp;

    /**
     * @var Account
     */
    public $user;

    /**
     * @var Media
     */
    public $media;

    /**
     * @var string
     */
    public $text;

    /**
     * As usual id is changed on each request, we can construct more stable one
     * @return string
     */
    public function getIdempotentId()
    {
        return $this->getType()
            . '_' . $this->user->getId()
            . '_' . (int)$this->getTimestamp()
            . ($this->media ? '_' . $this->media->getId() : '');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return Account
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $value
     * @param $prop
     * @param $props
     */

    protected function initPropertiesCustom($value, $prop, $props)
    {
        switch ($prop) {
            case 'id':
                $this->id = $value;
                break;
            case 'type':
                $this->type = $value;
                break;
            case '__typename':
                $this->typeName = isset($this->typeNames[$value]) ? $this->typeNames[$value] : $value;
                break;
            case 'timestamp':
                $this->timestamp = $value;
                break;
            case 'user':
                $this->user = Account::create($value);
                break;
            case 'media':
                $this->media = Media::create($value);
                break;
            case 'text':
                $this->text = $value;
                break;
        }
    }

}
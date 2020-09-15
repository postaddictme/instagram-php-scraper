<?php

namespace InstagramScraper\Model;

/**
 * Class Media
 * @package InstagramScraper\Model
 */
class ReelMedia extends AbstractModel
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $caption;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $expiringAt;

    /**
     * @var string
     */
    protected $image;

    /**
     * @var Account
     */
    protected $user;

    /**
     * @var Account[]
     */
    protected $mentions;

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
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getExpiringAt()
    {
        return $this->expiringAt;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return Account
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Account[]
     */
    public function getMentions()
    {
        return $this->mentions;
    }

    /**
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
            case 'caption':
                $this->caption = isset($arr[$prop]['text']) ? $arr[$prop]['text'] : null;
                break;
            case 'code':
                $this->code = $value;
                break;
            case 'expiring_at':
                $this->expiringAt = (int)$value;
                break;
            case 'reel_mentions':
                foreach ($arr[$prop] as $mention) {
                    if (!$mention['user']) {
                        continue;
                    }

                    $this->mentions[] = Account::create($mention['user']);
                }
                break;
            case 'user':
                $this->user = Account::create($arr[$prop]);
                break;
            case 'image_versions2':
                foreach ($arr[$prop]['candidates'] as $candidate) {
                    if (!$candidate['url']) {
                        continue;
                    }

                    $this->image = $candidate['url'];
                    break;
                }
                break;
        }
    }
}

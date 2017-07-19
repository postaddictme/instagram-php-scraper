<?php
/**
 * File:    InitializerTrait.php
 * Project: instagram-php-scraper
 * User:    evgen
 * Date:    19.07.17
 * Time:    11:50
 */

namespace InstagramScraper\Traits;


trait InitializerTrait
{

    /**
     * @var bool
     */
    protected $isNew = true;

    /**
     * @var bool
     */
    protected $isLoaded = false;

    /**
     * @var bool - init data was empty
     */
    protected $isLoadEmpty = true;

    /**
     * @var bool
     */
    protected $isFake = false;

    /**
     * @var int
     */
    protected $isAutoConstruct = false;

    /**
     * @var int
     */
    protected $modified;

    /**
     * Array of initialization data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param array $params
     *
     * @return static
     */
    public static function create(array $params = null)
    {
        return new static($params);
    }

    /**
     * @return $this
     */
    public static function fake()
    {
        return static::create()->_setFake(true);
    }

    /**
     * @param array $props
     */
    protected function __construct(array $props = null)
    {
        $this->_beforeInit();
        $this->modified = \time();
        if ($this->isAutoConstruct) {
            $this->_initAuto();
        } elseif (empty($props)) {
            $this->_initDefaults();
        } else {
            $this->_init($props);
        }
        $this->_afterInit();
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isLoadEmpty;
    }

    /**
     * @return bool
     */
    public function isFake()
    {
        return $this->isFake;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = [];
        $map = static::$initPropertiesMap;
        foreach ($map as $key => $init) {
            if (\property_exists($this, $key)) {
                //if there is property then it just assign value
                $ret[$key] = $this->{$key};
            } elseif (isset($this[$key])) {
                //probably array access
                $ret[$key] = $this[$key];
            } else {
                $ret[$key] = null;
            }
        }

        return $ret;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    protected function _setFake($value = true)
    {
        $this->isFake = (bool) $value;

        return $this;
    }

    /**
     * @param array $props
     *
     * @return $this
     */
    final protected function _init(array $props)
    {
        //?reflection?
        foreach ($props as $prop => $value) {
            if (\method_exists($this, 'initPropertiesCustom')) {
                \call_user_func([$this, 'initPropertiesCustom'], $value, $prop, $props);
            } elseif (isset(static::$initPropertiesMap[$prop])) {
                $methodOrProp = static::$initPropertiesMap[$prop];
                if (\method_exists($this, $methodOrProp)) {
                    //if there is method then use it firstly
                    \call_user_func([$this, $methodOrProp], $value, $prop, $props);
                } elseif (\property_exists($this, $methodOrProp)) {
                    //if there is property then it just assign value
                    $this->{$methodOrProp} = $value;
                } else {
                    //otherwise fill help data array
                    //for following initialization
                    $this->data[$methodOrProp] = $value;
                    $this->data[$prop] = $value;
                }
            } else {
                //otherwise fill help data array
                $this->data[$prop] = $value;
            }
        }
        $this->isNew = false;
        $this->isLoaded = true;
        $this->isLoadEmpty = false;

        return $this;
    }

    /**
     * @return $this
     */
    final protected function _initAuto()
    {
        foreach ($this as $prop => $value) {
            if (isset(static::$initPropertiesMap[$prop]) and $methodOrProp = static::$initPropertiesMap[$prop] and \method_exists($this,
                    $methodOrProp)
            ) {
                //if there is method then use it firstly
                \call_user_func([$this, $methodOrProp], $value, $prop);
            }
        }
        $this->isNew = false;
        $this->isLoaded = true;
        $this->isLoadEmpty = false;

        return $this;
    }

    /**
     * @return $this
     */
    protected function _initDefaults()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _beforeInit()
    {
        //TODO а зачем это надо? Как-то неочевидно
//        if (!empty(static::$initPropertiesMap)) {
//            static::$initPropertiesMap += [
//                'modified' => 'initModified',
//                'flags'    => 'initInt',
//            ];
//        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterInit()
    {
        return $this;
    }

    /**
     * @param $datetime
     *
     * @return $this
     */
    protected function initModified($datetime)
    {
        $this->modified = \strtotime($datetime);

        return $this;
    }

    /**
     * @param string $date
     * @param string $key
     *
     * @return $this
     */
    protected function initDatetime($date, $key)
    {
        return $this->_initProperty(\strtotime($date), $key);
    }

    /**
     * @param mixed  $value
     * @param string $key
     *
     * @return $this
     */
    protected function initBool($value, $key)
    {
        return $this->_initProperty(!empty($value), "is{$key}", $key);
    }

    /**
     * @param mixed  $value
     * @param string $key
     *
     * @return $this
     */
    protected function initInt($value, $key)
    {
        return $this->_initProperty((int) $value, $key);
    }

    /**
     * @param mixed  $value
     * @param string $key
     *
     * @return $this
     */
    protected function initFloat($value, $key)
    {
        return $this->_initProperty((float) $value, $key);
    }

    /**
     * @param string $rawData
     * @param string $key
     *
     * @return $this
     */
    protected function initJsonArray($rawData, $key)
    {
        $value = \json_decode($rawData, true);
        if (empty($value)) {
            //could not resolve -
            if ('null' === $rawData or '' === $rawData) {
                $value = [];
            } else {
                $value = (array) $rawData;
            }
        } else {
            $value = (array) $value;
        }

        return $this->_initProperty($value, $key);
    }

    /**
     * @param mixed  $value
     * @param string $key
     *
     * @return $this
     */
    protected function _initExplode($value, $key)
    {
        return $this->_initProperty(\explode(',', $value), "is{$key}", $key);
    }

    /**
     * @param $value
     * @param $key
     *
     * @return $this
     */
    protected function _initProperty($value, $key)
    {
        $keys = \func_get_args();
        unset($keys[0]); //remove value
        if (\count($keys) > 1) {
            foreach ($keys as $key) {
                if (\property_exists($this, $key)) { //first found set
                    $this->{$key} = $value;

                    return $this;
                }
            }
        } elseif (\property_exists($this, $key)) {
            $this->{$key} = $value;
        }

        return $this;
    }

}
<?php

declare(strict_types=1);

namespace InstagramScraper\Model;

use InstagramScraper\Traits\ArrayLikeTrait;
use InstagramScraper\Traits\InitializerTrait;

/**
 * Class AbstractModel
 */
abstract class AbstractModel implements \ArrayAccess
{
    use InitializerTrait;
    use ArrayLikeTrait;

    /**
     * @var array
     */
    protected static $initPropertiesMap = [];

    /**
     * @return array
     */
    public static function getColumns()
    {
        return array_keys(static::$initPropertiesMap);
    }
}

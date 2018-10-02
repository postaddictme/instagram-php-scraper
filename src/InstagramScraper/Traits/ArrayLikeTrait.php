<?php
/**
 * File:    ArrayLikeTrait.php
 * Project: instagram-php-scraper
 * User:    evgen
 * Date:    19.07.17
 * Time:    11:50
 */

namespace InstagramScraper\Traits;


/**
 *  ArrayAccess implementation
 */
trait ArrayLikeTrait
{

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->isMethod($offset, 'get') || \property_exists($this, $offset);
    }

    /**
     * @param $method
     * @param $case
     *
     * @return bool|string
     */
    protected function isMethod($method, $case)
    {
        $uMethod = $case . \ucfirst($method);
        if (\method_exists($this, $uMethod)) {
            return $uMethod;
        }
        if (\method_exists($this, $method)) {
            return $method;
        }
        return false;
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($run = $this->isMethod($offset, 'get')) {
            return $this->run($run);
        } elseif (\property_exists($this, $offset)) {
            if (isset($this->{$offset})) {
                return $this->{$offset};
            } elseif (isset($this::$offset)) {
                return $this::$offset;
            }
        }

        return null;
    }

    /**
     * @param $method
     *
     * @return mixed
     */
    protected function run($method)
    {
        if (\is_array($method)) {
            $params = $method;
            $method = \array_shift($params);
            if ($params) {
                return \call_user_func_array([$this, $method], $params);
            }
        }
        return \call_user_func([$this, $method]);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($run = $this->isMethod($offset, 'set')) {
            $this->run($run);
        } else {
            $this->{$offset} = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($run = $this->isMethod($offset, 'unset')) {
            $this->run($run);
        } else {
            $this->{$offset} = null;
        }
    }

}

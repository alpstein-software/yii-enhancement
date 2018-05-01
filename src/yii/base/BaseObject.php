<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\base;

/**
 * Class BaseObject
 * @package alpstein\yii\base
 */
class BaseObject extends \yii\base\BaseObject
{
    /**
     * @var array to hold the runtime data
     */
    private $__data = [];

    /**
     * check if the data exist
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        return isset($this->__data[$key]) || array_key_exists($key, $this->__data);
    }

    /**
     * get the data stored inside the data cached
     *
     * @param string $key
     * @param null|mixed $default
     * @return mixed|null
     */
    public function getData($key, $default = null)
    {
        return isset($this->__data[$key]) ? $this->__data[$key] : $default;
    }

    /**
     * Store the data into data cached
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed|null
     */
    public function setData($key, $value)
    {
        return $this->__data[$key] = $value;
    }

    /**
     * Short cut for reading runtime cached data.
     *
     * @param string $key
     * @param callback $callback
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function retrieveData($key, $callback, $default = null)
    {
        if (!$this->hasData($key)) {
            $data = call_user_func($callback);
            $this->setData($key, $data);
        }
        return $this->getData($key, $default);
    }

    /**
     * reset the runtime cache
     * return $this
     */
    public function flushData()
    {
        $this->__data = [];
        return $this;
    }
}


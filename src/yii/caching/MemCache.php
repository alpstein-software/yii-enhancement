<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\caching;

/**
 * Class MemCache
 * @package alpstein\yii\caching
 */
class MemCache extends \yii\caching\MemCache
{
    /**
     * Retrieve data from cache or generate the content if cache not found or expired
     * a short cut for get and set the value from cache
     *
     * @param mixed $key a key identifying the value to be cached. This can be a simple string or
     * a complex data structure consisting of factors representing the key.
     * @param mixed $callback the callback function that return data to be cache
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @param \yii\caching\Dependency $dependency dependency of the cached item. If the dependency changes,
     * the corresponding value in the cache will be invalidated when it is fetched via [[get()]].
     * This parameter is ignored if [[serializer]] is false.
     * @return mixed the data cached
     */
    public function retrieve($key, $callback, $duration = 0, $dependency = null)
    {
        if (($data = $this->get($key)) === false) {
            $data = call_user_func($callback);
            $this->set($key, $data, $duration, $dependency);
        }
        return $data;
    }
}

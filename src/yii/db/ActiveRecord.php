<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\db;

/**
 * Class ActiveRecord
 * @package alpstein\yii\db
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    /**
     * @var array
     */
    private $__data = [];

    /**
     * @return bool
     */
    public function softDelete()
    {
        if ($this->hasAttribute('is_active')) {
            $this->setAttribute('is_active', false);
            return $this->save(false);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function toggleActive()
    {
        if ($this->hasAttribute('is_active')) {
            $value = (bool) $this->getAttribute('is_active');
            $this->setAttribute('is_active', !$value);
            return $this->save(false);
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        if ($this->hasAttribute('is_active')) {
            return (bool) $this->getAttribute('is_active');
        }

        return false;
    }

    /**
     * @inheritdoc
     * @param array $fields
     * @param array $expand
     * @return array
     */
    protected function resolveFields(array $fields, array $expand)
    {
        $result = [];

        foreach ($this->fields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (empty($fields) || in_array($field, $fields, true)) {
                $result[$field] = $definition;
            }
        }

        if (empty($expand)) {
            return $result;
        }

        foreach ($this->extraFields() as $field => $definition) {
            if (is_int($field)) {
                $field = $definition;
            }
            if (in_array('*', $expand, true) || in_array($field, $expand, true)) {
                $result[$field] = $definition;
            }
        }

        return $result;
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
            $this->writeData($key, $data);
        }
        return $this->readData($key, $default);
    }

    /**
     * @param $key
     * @return boolean
     */
    public function hasData($key)
    {
        return isset($this->__data[$key]) || array_key_exists($key, $this->__data);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function writeData($key, $value)
    {
        $this->__data[$key] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return bool|mixed
     */
    public function readData($key, $default = false)
    {
        if ($this->hasData($key)) {
            return $this->__data[$key];
        } else {
            return $default;
        }
    }
}


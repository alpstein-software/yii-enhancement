<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\db;

/**
 * Class ActiveQuery
 * @package alpstein\yii\db
 */
class ActiveQuery extends \yii\db\ActiveQuery
{
    /**
     * @var string
     */
    private $__alias;

    /**
     * @param int $value
     * @return $this
     */
    public function id($value)
    {
        return $this->andWhere([$this->getColumnName('id') => $value]);
    }

    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere([$this->getColumnName('is_active') => true]);
    }

    /**
     * @param string $column
     * @return string
     */
    protected function getColumnName($column)
    {
        $alias = $this->getAlias();
        if (!empty($alias)) {
            return $alias . '.[[' . $column . ']]';
        }

        return '[[' . $column . ']]';
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        if (isset($this->__alias)) {
            return $this->__alias;
        }

        /* @var $modelClass \yii\db\ActiveRecord */
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();

        foreach ((array) $this->from as $key => $table) {
            if ($table === $tableName) {
                if (is_string($key)) {
                    return $this->__alias = $key;
                }
            }
        }

        return $tableName;
    }
}

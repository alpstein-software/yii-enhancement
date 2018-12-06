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
     * @inheritdoc
     */
    public function alias($alias)
    {
        $this->setAlias($alias);
        return parent::alias($alias);
    }

    /**
     * @param string $value
     */
    protected function setAlias($value)
    {
        $this->replaceAlias($this->where, $this->getAlias(), $value);
        $this->__alias = $value;
    }

    /**
     * @param string|array $where
     * @param string $from
     * @param string $to
     */
    protected function replaceAlias(&$where, $from, $to)
    {
        if (is_array($where)) {
            foreach ($where as $key => &$value) {
                if (is_array($value)) {
                    $this->replaceAlias($value, $from, $to);
                } elseif (is_string($key) && strpos($key, $from . '.') !== false) {
                    $newKey = str_replace($from . '.', $to . '.', $key);
                    $where[$newKey] = $value;
                    unset($where[$key]);
                }
                unset($value);//clean up pointer reference
            }
        }
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

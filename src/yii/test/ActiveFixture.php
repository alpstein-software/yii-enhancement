<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\test;

use yii\helpers\Inflector;

/**
 * Class ActiveFixture
 * @package alpstein\yii\test
 */
class ActiveFixture extends \yii\test\ActiveFixture
{
    /**
     * Try to load the data base on the class name camel to id case
     * e.g.:
     *   User -> user.php
     *   UserVoucher -> user-voucher.php
     * @inheritdoc
     */
    protected function getData()
    {
        if ($this->dataFile === null) {
            $className = (new \ReflectionClass($this->modelClass))->getShortName();
            $dataFileName = Inflector::camel2id($className) . '.php';

            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $dataFileName;

            return $this->loadData($dataFile, false);
        }

        return parent::getData();
    }
}

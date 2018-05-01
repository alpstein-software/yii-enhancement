<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\codeception\test;

use Faker\Factory;

/**
 * Class Unit
 * @package alpstein\yii\codeception\test
 *
 * @method void assertFalse($text)
 */
class Unit extends \Codeception\Test\Unit
{
    /**
     * @var \bapi\tests\UnitTester
     */
    protected $tester;
    /**
     * @var null|\Faker\Generator
     */

    protected $faker;

    /**
     * initialize fake controller and module for Url::to() helper function to be test correctly
     * @Override
     */
    protected function _before()
    {
        //\Yii::$app->controller = new Controller('test', new Module('test'));
    }

    /**
     * @return \Faker\Generator
     */
    protected function getFaker()
    {
        if (isset($this->faker)) {
            return $this->faker;
        }

        return $this->faker = Factory::create();
    }
}

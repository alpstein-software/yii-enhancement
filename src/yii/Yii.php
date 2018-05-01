<?php
/**
 * Yii bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require(__DIR__ . '/../../vendor/yiisoft/yii2/BaseYii.php');

/**
 * Yii is a helper class serving common framework functionaries.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var \alpstein\yii\web\Application the application instance
     */
    public static $app;
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require(__DIR__ . '/../../vendor/yiisoft/yii2/classes.php');
Yii::$container = new yii\di\Container();

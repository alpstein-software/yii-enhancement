<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\helpers;

/**
 * Class Url
 * @package alpstein\yii\helpers
 */
class Url extends \yii\helpers\Url
{
    public static function normalizeRoute($route)
    {
        return parent::normalizeRoute($route);
    }
}
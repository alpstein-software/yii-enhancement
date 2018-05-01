<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\helpers;

/**
 * Class Json
 * @package alpstein\yii\helpers
 */
class Json extends \yii\helpers\Json
{
    /**
     * Validate is the given string is JSON
     * @param string $content
     * @return bool
     */
    public static function validate($content)
    {
        return substr($content, 0, 1) === '{';
    }
}

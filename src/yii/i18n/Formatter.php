<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\i18n;

/**
 * Class Formatter
 * @package alpstein\yii\i18n
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * @param string $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asString($value)
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    /**
     * @param mixed $value
     * @param int $decimals
     * @return string
     */
    public function asRoundNumber($value, $decimals = 0)
    {
        return number_format($value, $decimals, '.', '');
    }
}

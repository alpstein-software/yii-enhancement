<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\helpers;

/**
 * Class StringHelper
 * @package alpstein\yii\helpers
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * @param string $value
     * @param bool $trim
     * @param bool $skipEmpty
     * @return array
     */
    public static function explodeByComma($value, $trim = true, $skipEmpty = true)
    {
        if ($trim && $skipEmpty) {
            return preg_split('/\s*,\s*/', $value, null, PREG_SPLIT_NO_EMPTY);
        }

        if ($trim) {
            return preg_split('/\s*,\s*/', $value);
        }

        return explode(',', $value);
    }

    /**
     * This function will return integer from a version string
     * @param string $version
     * @return int
     */
    public static function resolveVersion($version)
    {
        if (is_scalar($version)) {
            $digits = preg_replace('/[^0-9]/', '', $version);
            return (int) $digits;
        }

        return 0;
    }

    /**
     * return the first character of each words in string
     * @param string $value
     * @return string
     */
    public static function shortForm($value)
    {
        $s = strtolower($value);
        $s = ucwords($s);
        return preg_replace('/[a-z\s+]/', '', $s);
    }

    /**
     * @param int $length
     * @param bool $alpha
     * @param bool $digit
     * @param string $case
     * @return string
     */
    public static function randomString($length = 10, $alpha = true, $digit = true, $case = 'lower')
    {
        $digits = '0123456789';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        //make sure at least one is selected
        if (!$alpha && !$digit) {
            $alpha = true;
            $digit = true;
        }

        $characters = '';
        if ($alpha) {
            $case = strtolower($case);
            if (!in_array($case, ['lower', 'upper', 'mix'])) {
                $case = 'lower';
            }

            switch ($case) {
                case 'lower':
                    $characters .= $lower;
                    break;
                case 'upper':
                    $characters .= $upper;
                    break;
                case 'mix':
                    $characters .= $lower . $upper;
                    break;
                default:
                    $characters .= $lower;
                    break;
            }
        }

        $characters .= $digit ? $digits : '';

        //the output
        $output = '';

        $len = strlen($characters);
        for ($p = 0; $p < $length; $p++) {
            $output .= $characters[mt_rand(0, $len-1)];
        }

        return $output;
    }
}

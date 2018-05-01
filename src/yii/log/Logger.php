<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\log;

/**
 * Class Logger
 * @package alpstein\yii\log
 */
class Logger extends \yii\log\Logger
{
    /**
     * @param array|string $message
     * @param int $level
     * @param string $category
     */
    public function log($message, $level, $category = 'application')
    {
        if (defined('YII_CONSOLE_MODE') && YII_CONSOLE_MODE) {
            if ($level <= static::LEVEL_INFO && $category == 'application' && is_string($message)) {
                echo sprintf('Console: %s: %s - %s', self::getLevelName($level), $category, $message) . "\n";
            }
        }

        parent::log($message, $level, $category);
    }
}
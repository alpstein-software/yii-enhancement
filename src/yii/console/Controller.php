<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\console;

use yii\console\controllers\HelpController;
use yii\helpers\Console;

/**
 * Class Controller
 * @package alpstein\yii\console
 */
class Controller extends \yii\console\Controller
{
    /**
     * @var int
     */
    public $confirm = 0;

    /**
     * @var string
     */
    public $defaultAction = 'help';

    /**
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['confirm']
        );
    }
    /**
     * Print this command's help
     * @throws \yii\console\Exception
     */
    public function actionHelp()
    {
        $cmd = new HelpController('help', null);
        $cmd->actionIndex($this->getUniqueId());
    }

    /**
     * @return bool
     */
    protected function getIsConfirm()
    {
        return (bool) $this->confirm;
    }

    /**
     * @param string $text
     */
    protected function info($text)
    {
        echo $this->ansiFormat($text, Console::FG_BLUE) . "\n";
    }

    /**
     * @param string $text
     */
    protected function success($text)
    {
        echo $this->ansiFormat($text, Console::FG_GREEN) . "\n";
    }

    /**
     * @param string $text
     */
    protected function warning($text)
    {
        echo $this->ansiFormat($text, Console::FG_YELLOW) . "\n";
    }

    /**
     * @param string $text
     */
    protected function error($text)
    {
        echo $this->ansiFormat($text, Console::FG_RED) . "\n";
    }

    /**
     * @param string $text
     */
    protected function special($text)
    {
        echo $this->ansiFormat($text, Console::FG_CYAN) . "\n";
    }

    /**
     * @param string $text
     */
    protected function progress($text)
    {
        echo "\r" . str_repeat(' ', 100);
        echo "\r" . $this->ansiFormat($text, Console::FG_GREY);
    }

    /**
     * @param string $text
     * @param int $color
     */
    protected function trace($text, $color = Console::FG_GREY)
    {
        if (YII_DEBUG) {
            echo $this->ansiFormat($text, $color) . "\n";
        }
    }
}

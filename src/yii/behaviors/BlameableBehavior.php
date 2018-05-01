<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\behaviors;

use Yii;

/**
 * Class BlameableBehavior
 * @package alpstein\yii\behaviors
 */
class BlameableBehavior extends \yii\behaviors\BlameableBehavior
{
    /**
     * @inheritdoc
     * @param \yii\base\Event $event
     */
    protected function getValue($event)
    {
        if (ALPSTEIN_CONSOLE_MODE) {
            return -1;//console mode
        }

        if (Yii::$app->user->isGuest) {
            return 0;
        }
        
        return parent::getValue($event);
    }
}

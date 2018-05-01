<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\queue\db;

use alpstein\yii\queue\cli\VerboseBehavior;

/**
 * Class Command
 * @package alpstein\yii\queue\db
 */
class Command extends \yii\queue\db\Command
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $valid = parent::beforeAction($action);

        if ($this->canVerbose($action->id) && $this->verbose) {
            $this->queue->attachBehavior('verbose', [
                'class' => VerboseBehavior::class,
                'command' => $this,
            ]);
        }

        return $valid;
    }
}

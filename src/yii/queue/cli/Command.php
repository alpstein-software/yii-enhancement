<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\queue\cli;

/**
 * Class Command
 * @package alpstein\yii\queue\cli
 */
class Command extends \yii\queue\cli\Command
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

    /**
     * @inheritdoc
     */
    protected function isWorkerAction($actionID)
    {
        return in_array($actionID, ['run' ,'listen'], true);
    }
}

<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\yii\queue\cli;

use common\base\queue\jobs\Task;
use yii\queue\ExecEvent;

/**
 * Class Verbose
 * @package alpstein\yii\queue\cli
 */
class VerboseBehavior extends \yii\queue\cli\VerboseBehavior
{
    /**
     * @param ExecEvent $event
     * @return string
     */
    protected function jobTitle(ExecEvent $event)
    {
        if ($event->job instanceof Task) {
            $message = $event->job->getVerboseMessage();
            $extra = "attempt: $event->attempt";
            if ($pid = $event->sender->getWorkerPid()) {
                $extra .= ", pid: $pid";
            }
            return " [$event->id] $message ($extra)";
        }

        return parent::jobTitle($event);
    }
}

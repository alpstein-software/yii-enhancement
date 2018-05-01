<?php

/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */
namespace alpstein\yii\queue\core;

use alpstein\yii\queue\cli\Command;
use yii\base\InvalidConfigException;
use yii\console\Application as ConsoleApp;
use yii\queue\cli\Queue as BaseQueue;
use Yii;

/**
 * Class Queue
 * @package alpstein\yii\queue\core
 */
class Queue extends BaseQueue
{
    /**
     * @var string
     */
    public $driver;
    /**
     * @var array
     */
    public $driverOptions = [];
    /**
     * @var string command class name
     */
    public $commandClass = Command::class;
    /**
     * @var \yii\queue\Queue
     */
    private $_queue;

    /**
     * ensure proper configuration
     */
    public function init()
    {
        parent::init();

        if (!isset($this->driver) || !is_string($this->driver)) {
            throw new InvalidConfigException('$driver must be set, and must be a string !');
        }

        if (!isset($this->driverOptions[$this->driver]) || !is_array($this->driverOptions[$this->driver])) {
            throw new InvalidConfigException('$driverOptions for ' . $this->driver . ' must be defined !');
        }

        if (isset($this->driverOptions[$this->driver]['commandClass'])) {
            $this->commandClass = $this->driverOptions[$this->driver]['commandClass'];
        }
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if ($app instanceof ConsoleApp) {
            $app->controllerMap[$this->getCommandId()] = [
                    'class' => $this->commandClass,
                    'queue' => $this->getQueueComponent(),
                ] + $this->commandOptions;
        }
    }

    /**
     * @inheritdoc
     */
    public function status($id)
    {
        return $this->getQueueComponent()->status($id);
    }

    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        return $this->getQueueComponent()->pushMessage($message, $ttr, $delay, $priority);
    }

    /**
     * @return \yii\queue\Queue
     */
    protected function getQueueComponent()
    {
        if (isset($this->_queue)) {
            return $this->_queue;
        }

        if (isset($this->driverOptions[$this->driver])) {
            /** @var \yii\queue\sync\Queue $queue */
            $queue = Yii::createObject($this->driverOptions[$this->driver]);
            return $this->_queue = $queue;
        }

        return $this->_queue = $this->getFallbackQueueComponent();
    }

    /**
     * @return \yii\queue\Queue
     */
    protected function getFallbackQueueComponent()
    {
        /** @var \yii\queue\sync\Queue $queue */
        $queue = Yii::createObject(['class' => 'yii\queue\sync\Queue', 'handle' => true]);
        return $queue;
    }
}

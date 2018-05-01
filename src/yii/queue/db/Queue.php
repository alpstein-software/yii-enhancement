<?php

/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */
namespace alpstein\yii\queue\db;

/**
 * Class Queue
 * @package alpstein\yii\queue\db
 */
class Queue extends \yii\queue\db\Queue
{
    /**
     * @var bool
     */
    public $enabled = true;
    /**
     * @var string table name
     */
    public $tableName = '{{%system_queue}}';
    /**
     * @var string command class name
     */
    public $commandClass = Command::class;

    /**
     * @inheritdoc
     */
    protected function pushMessage($message, $ttr, $delay, $priority)
    {
        if ($this->enabled === false) {
            return 0;
        }

        try {
            $code = md5($this->channel . $message);
            $this->db->createCommand()->insert($this->tableName, [
                'code' => $code,
                'channel' => $this->channel,
                'job' => $message,
                'pushed_at' => time(),
                'ttr' => $ttr,
                'delay' => $delay,
                'priority' => $priority ?: 1024,
            ])->execute();
            $tableSchema = $this->db->getTableSchema($this->tableName);
            $id = $this->db->getLastInsertID($tableSchema->sequenceName);

            return $id;
        } catch (\Exception $e) {
            //ignore, mostly code duplicate
            \Yii::error($e);
        }

        return 0;
    }
}

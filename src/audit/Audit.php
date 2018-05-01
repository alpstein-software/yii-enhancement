<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit;

use alpstein\audit\models\AuditEntry;
use alpstein\yii\base\BaseObject;
use yii\base\Application;
use Yii;

/**
 * Class Audit
 * @package alpstein\services
 */
class Audit extends BaseObject
{
    /**
     * @var AuditEntry|null
     */
    private $_entry;

    /**
     * initialize and register action
     */
    public function init()
    {
        parent::init();

        $app = Yii::$app;
        //$app->on(Application::EVENT_BEFORE_ACTION, [$this, 'onBeforeAction']);
        $app->on(Application::EVENT_AFTER_REQUEST, [$this, 'onAfterRequest']);
    }

    /**
     * @param bool $create
     * @param bool $new
     * @return AuditEntry
     */
    public function getEntry($create = false, $new = false)
    {
        $entry = new AuditEntry();
        $tableSchema = $entry->getDb()->schema->getTableSchema($entry->tableName());
        if ($tableSchema) {
            if ((!$this->_entry && $create) || $new) {
                $this->_entry = AuditEntry::create(true);
            }
        }
        return $this->_entry;
    }

    /**
     * finalizing the entry
     */
    public function onAfterRequest()
    {
        if ($this->_entry) {
            $this->_entry->finalize();
        }
    }

    /**
     * @return int|mixed|null|string
     */
    public static function getUserId()
    {
        return (Yii::$app instanceof \yii\web\Application && Yii::$app->user) ? Yii::$app->user->id : null;
    }
}

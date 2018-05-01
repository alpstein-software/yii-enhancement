<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\behaviors;

use alpstein\audit\Audit;
use alpstein\yii\DateTime;
use alpstein\audit\models\AuditEntry;
use alpstein\audit\models\AuditTrail;
use yii\web\Application;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use Yii;

/**
 * Class AuditTrailBehavior
 * @property ActiveRecord $owner
 * @package alpstein\yii\behaviors
 */
class AuditTrailBehavior extends Behavior
{
    /**
     * @var string|Connection
     */
    public $db = 'db';
    /**
     * @var string|Audit
     */
    public $audit = 'audit';

    /**
     * Array with fields to save
     * You don't need to configure both `allowed` and `ignored`
     * @var array
     */
    public $allowed = [];

    /**
     * Array with fields to ignore
     * You don't need to configure both `allowed` and `ignored`
     * @var array
     */
    public $ignored = [];

    /**
     * Array with classes to ignore
     * @var array
     */
    public $ignoredClasses = [];

    /**
     * Timestamp attributes should, in most cases, be ignored. If both AudittrailBehavior and
     * TimestampBehavior logs the created_at and updated_at fields, the data is saved twice.
     * In case you want to log them, you can unset the column from this timestamp column name suggestions.
     * Set to null to disable this filter and log all columns.
     * @var null|array
     */
    public $timestamp_fields = ['created', 'updated', 'created_at', 'updated_at', 'timestamp', 'created_by', 'updated_by'];

    /**
     * Is the behavior is active or not
     * @var boolean
     */
    public $active = true;

    /**
     * @var array
     */
    private $_oldAttributes = [];

    /**
     * Array with fields you want to override before saving the row into audit_trail table
     * @var array
     */
    public $override = [];

    /**
     * initialize
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->audit = Instance::ensure($this->audit, Audit::class);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     *
     */
    public function afterFind()
    {
        $this->setOldAttributes($this->owner->getAttributes());
    }

    /**
     *
     */
    public function afterInsert()
    {
        $this->audit('CREATE');
        $this->setOldAttributes($this->owner->getAttributes());
    }

    /**
     *
     */
    public function afterUpdate()
    {
        $this->audit('UPDATE');
        $this->setOldAttributes($this->owner->getAttributes());
    }

    /**
     *
     */
    public function afterDelete()
    {
        $this->audit('DELETE');
        $this->setOldAttributes([]);
    }

    /**
     * @param $action
     * @throws \yii\db\Exception
     */
    public function audit($action)
    {
        // Not active? get out of here
        if (!$this->active) {
            return;
        }

        // Lets check if the whole class should be ignored
        if (sizeof($this->ignoredClasses) > 0 && array_search(get_class($this->owner), $this->ignoredClasses) !== false) {
            return;
        }

        // If this is a delete then just write one row and get out of here
        if ($action == 'DELETE') {
            $this->saveAuditTrailDelete();
            return;
        }

        // Now lets actually write the attributes
        $this->auditAttributes($action);
    }

    /**
     * Clean attributes of fields that are not allowed or ignored.
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributes($attributes)
    {
        $attributes = $this->cleanAttributesAllowed($attributes);
        $attributes = $this->cleanAttributesIgnored($attributes);
        $attributes = $this->cleanAttributesOverride($attributes);
        return $attributes;
    }

    /**
     * Unset attributes which are not allowed
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributesAllowed($attributes)
    {
        if (sizeof($this->allowed) > 0) {
            foreach ($attributes as $f => $v) {
                if (array_search($f, $this->allowed) === false) {
                    unset($attributes[$f]);
                }
            }
        }
        return $attributes;
    }

    /**
     * Unset attributes which are ignored
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributesIgnored($attributes)
    {
        if (is_array($this->timestamp_fields) && count($this->timestamp_fields) > 0) {
            $this->ignored = array_merge($this->ignored, $this->timestamp_fields);
        }
        if (count($this->ignored) > 0) {
            foreach ($attributes as $f => $v) {
                if (array_search($f, $this->ignored) !== false) {
                    unset($attributes[$f]);
                }
            }
        }
        return $attributes;
    }

    /**
     * attributes which need to get override with a new value
     *
     * @param $attributes
     * @return mixed
     */
    protected function cleanAttributesOverride($attributes)
    {
        if (sizeof($this->override) > 0 && sizeof($attributes) > 0) {
            foreach ($this->override as $field => $queryParams) {
                $newOverrideValues = $this->getNewOverrideValues($attributes[$field], $queryParams);
                $saveField = ArrayHelper::getValue($queryParams, 'saveField', $field);
                if (count($newOverrideValues) >1) {
                    $attributes[$saveField] = implode(', ', ArrayHelper::map($newOverrideValues, $queryParams['returnField'], $queryParams['returnField']));
                } elseif (count($newOverrideValues) == 1) {
                    $attributes[$saveField] = $newOverrideValues[0][$queryParams['returnField']];
                }
            }
        }
        return $attributes;
    }

    /**
     * @param string $searchFieldValue
     * @param array $queryParams
     * @return mixed
     */
    private function getNewOverrideValues($searchFieldValue, $queryParams)
    {
        $query = new Query;
        $query->select($queryParams['returnField'])
            ->from($queryParams['tableName'])
            ->where([$queryParams['searchField'] => $searchFieldValue]);
        $rows = $query->all();
        return $rows;
    }

    /**
     * @param string $action
     * @throws \yii\db\Exception
     */
    protected function auditAttributes($action)
    {
        // Get the new and old attributes
        $newAttributes = $this->cleanAttributes($this->owner->getAttributes());
        $oldAttributes = $this->cleanAttributes($this->getOldAttributes());

        // ensure to handle serialized attributes properly
        foreach ($newAttributes as $key => $value) {
            if (is_array($newAttributes[$key])) {
                $newAttributes[$key] = Json::encode($newAttributes[$key]);
            }
        }

        foreach ($oldAttributes as $key => $value) {
            if (is_array($oldAttributes[$key])) {
                $oldAttributes[$key] = Json::encode($oldAttributes[$key]);
            }
        }

        // If no difference then get out of here
        if (count(array_diff_assoc($newAttributes, $oldAttributes)) <= 0) {
            return;
        }

        // Get the trail data
        $entry_id = $this->getAuditEntryId();
        $user_id = $this->getUserId();
        $model = $this->owner->className();
        $model_id = $this->getNormalizedPk();
        $created = DateTime::getCurrentDateTime();
        $this->saveAuditTrail($action, $newAttributes, $oldAttributes, $entry_id, $user_id, $model, $model_id, $created);
    }

    /**
     * Save the audit trails for a create or update action
     *
     * @param $action
     * @param $newAttributes
     * @param $oldAttributes
     * @param $entry_id
     * @param $user_id
     * @param $model
     * @param $model_id
     * @param $created
     * @throws \yii\db\Exception
     */
    protected function saveAuditTrail($action, $newAttributes, $oldAttributes, $entry_id, $user_id, $model, $model_id, $created)
    {
        // Build a list of fields to log
        $rows = [];
        foreach ($newAttributes as $field => $new) {
            $old = isset($oldAttributes[$field]) ? $oldAttributes[$field] : '';
            // If they are not the same lets write an audit log
            if ($new != $old) {
                $rows[] = [$entry_id, $user_id, $old, $new, $action, $model, $model_id, $field, $created];
            }
        }
        // Record the field changes with a batch insert
        if (!empty($rows)) {
            $columns = ['entry_id', 'user_id', 'old_value', 'new_value', 'action', 'model', 'model_id', 'field', 'created_at'];
            $this->db->createCommand()->batchInsert(AuditTrail::tableName(), $columns, $rows)->execute();
        }
    }

    /**
     * Save the audit trails for a delete action
     */
    protected function saveAuditTrailDelete()
    {
        $this->db->createCommand()->insert(AuditTrail::tableName(), [
            'action' => 'DELETE',
            'entry_id' => $this->getAuditEntryId(),
            'user_id' => $this->getUserId(),
            'model' => $this->owner->className(),
            'model_id' => $this->getNormalizedPk(),
            'created_at' => DateTime::getCurrentDateTime(),
        ])->execute();
    }

    /**
     * @return array
     */
    public function getOldAttributes()
    {
        return $this->_oldAttributes;
    }

    /**
     * @param $value
     */
    public function setOldAttributes($value)
    {
        $this->_oldAttributes = $value;
    }

    /**
     * @return string
     */
    protected function getNormalizedPk()
    {
        $pk = $this->owner->getPrimaryKey();
        return is_array($pk) ? json_encode($pk) : $pk;
    }

    /**
     * @return int|null|string
     */
    protected function getUserId()
    {
        return (Yii::$app instanceof Application && Yii::$app->user) ? Yii::$app->user->id : null;
    }

    /**
     * @return int
     */
    protected function getAuditEntryId()
    {
        return Yii::$app->audit->getEntry(true)->id;
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
}

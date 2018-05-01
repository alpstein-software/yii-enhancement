<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\models;

use alpstein\audit\Audit;
use alpstein\yii\DateTime;
use alpstein\yii\db\ActiveRecord;
use yii\console\Request as ConsoleRequest;
use yii\web\Application as WebApplication;
use yii\web\Request as WebRequest;
use Yii;

/**
 * This is the model class for table "{{%audit_entry}}".
 *
 * @property int $id
 * @property int $user_id
 * @property double $duration
 * @property string $ip_address
 * @property string $request_method
 * @property int $ajax
 * @property string $route
 * @property int $memory_max
 * @property string $created_at
 *
 * @property AuditTrail[] $auditTrails
 */
class AuditEntry extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%audit_entry}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'memory_max'], 'integer'],
            [['duration'], 'number'],
            [['created_at'], 'required'],
            [['created_at'], 'safe'],
            [['ip_address'], 'string', 'max' => 45],
            [['request_method'], 'string', 'max' => 16],
            [['ajax'], 'string', 'max' => 1],
            [['route'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'duration' => 'Duration',
            'ip_address' => 'Ip Address',
            'request_method' => 'Request Method',
            'ajax' => 'Ajax',
            'route' => 'Route',
            'memory_max' => 'Memory Max',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @param bool $initialise
     * @return static
     */
    public static function create($initialise = true)
    {
        $entry = new static;
        if ($initialise) {
            $entry->record();
        }

        return $entry;
    }

    /**
     * Records the current application state into the instance.
     */
    public function record()
    {
        $app = Yii::$app;
        $request = $app->request;
        $this->route = $app->requestedAction ? $app->requestedAction->uniqueId : null;

        if ($request instanceof WebRequest) {
            $this->user_id = (Yii::$app instanceof WebApplication && Yii::$app->user) ? Yii::$app->user->id : null;
            $this->ip_address = $this->getIPAddress();
            $this->ajax = $request->isAjax;
            $this->request_method = $request->method;
            $this->created_at = DateTime::getCurrentDateTime();
        } elseif ($request instanceof ConsoleRequest) {
            $this->request_method = 'CLI';
        }
        $this->save(false);
    }

    /**
     * @return bool
     */
    public function finalize()
    {
        $app = Yii::$app;
        $request = $app->request;
        if (!$this->user_id && $request instanceof WebRequest) {
            $this->user_id = Audit::getUserId();
        }
        $this->duration = microtime(true) - YII_BEGIN_TIME;
        $this->memory_max = memory_get_peak_usage();
        return $this->save(false, ['duration', 'memory_max', 'user_id']);
    }

    /**
     * @return string
     */
    public function getIPAddress()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return current(array_values(array_filter(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']))));
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails()
    {
        return $this->hasMany(AuditTrail::class, ['entry_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return AuditEntryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuditEntryQuery(get_called_class());
    }
}

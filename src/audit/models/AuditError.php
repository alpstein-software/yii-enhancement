<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\models;

use alpstein\audit\components\Helper;
use alpstein\yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%audit_error}}".
 *
 * @property int $id
 * @property int $entry_id
 * @property string $message
 * @property int $code
 * @property string $file
 * @property int $line
 * @property resource $trace
 * @property string $hash
 * @property int $emailed
 * @property string $created_at
 */
class AuditError extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%audit_error}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entry_id', 'code', 'line'], 'integer'],
            [['message', 'created_at'], 'required'],
            [['message', 'trace'], 'string'],
            [['created_at'], 'safe'],
            [['file'], 'string', 'max' => 512],
            [['hash'], 'string', 'max' => 64],
            [['emailed'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entry_id' => 'Entry ID',
            'message' => 'Message',
            'code' => 'Code',
            'file' => 'File',
            'line' => 'Line',
            'trace' => 'Trace',
            'hash' => 'Hash',
            'emailed' => 'Emailed',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return mixed
     */
    public function getTraceArray()
    {
        return @unserialize($this->trace);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->trace = @serialize($this->trace);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     * @return AuditErrorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuditErrorQuery(get_called_class());
    }
}

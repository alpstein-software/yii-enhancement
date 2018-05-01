<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\audit\models;

use alpstein\yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%audit_trail}}".
 *
 * @property int $id
 * @property int $entry_id
 * @property int $user_id
 * @property string $action
 * @property string $model
 * @property string $model_id
 * @property string $field
 * @property string $old_value
 * @property string $new_value
 * @property string $created_at
 *
 * @property AuditEntry $entry
 */
class AuditTrail extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%audit_trail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entry_id', 'user_id'], 'integer'],
            [['action', 'model', 'model_id', 'created_at'], 'required'],
            [['old_value', 'new_value'], 'string'],
            [['created_at'], 'safe'],
            [['action', 'model', 'model_id', 'field'], 'string', 'max' => 255],
            [['entry_id'], 'exist', 'skipOnError' => true, 'targetClass' => AuditEntry::className(), 'targetAttribute' => ['entry_id' => 'id']],
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
            'user_id' => 'User ID',
            'action' => 'Action',
            'model' => 'Model',
            'model_id' => 'Model ID',
            'field' => 'Field',
            'old_value' => 'Old Value',
            'new_value' => 'New Value',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return mixed
     */
    public function getDiffHtml()
    {
        $old = explode("\n", $this->old_value);
        $new = explode("\n", $this->new_value);
        foreach ($old as $i => $line) {
            $old[$i] = rtrim($line, "\r\n");
        }
        foreach ($new as $i => $line) {
            $new[$i] = rtrim($line, "\r\n");
        }
        $diff = new \Diff($old, $new);
        return $diff->render(new \Diff_Renderer_Html_Inline);
    }

    /**
     * @return ActiveRecord|bool
     */
    public function getParent()
    {
        /** @var ActiveRecord $parentModel */
        $parentModel = new $this->model;
        $parent = $parentModel::findOne($this->model_id);
        return $parent ? $parent : $parentModel;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntry()
    {
        return $this->hasOne(AuditEntry::class, ['id' => 'entry_id']);
    }

    /**
     * @inheritdoc
     * @return AuditTrailQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuditTrailQuery(get_called_class());
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mode".
 *
 * @property integer $id
 * @property integer $group_id
 * @property string $key
 * @property string $name
 *
 * @property ModeGroup $group
 */
class Mode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mode';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'key', 'name'], 'required'],
            [['group_id'], 'integer'],
            [['key'], 'string', 'max' => 16],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['key'], 'unique'],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModeGroup::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'key' => 'Key',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(ModeGroup::className(), ['id' => 'group_id']);
    }
}

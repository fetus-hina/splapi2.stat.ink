<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mode_group".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Mode[] $modes
 */
class ModeGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mode_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModes()
    {
        return $this->hasMany(Mode::className(), ['group_id' => 'id']);
    }
}

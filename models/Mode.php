<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mode".
 *
 * @property integer $id
 * @property string $key
 * @property string $name
 *
 * @property League[] $leagues
 * @property Ranked[] $rankeds
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
            [['key', 'name'], 'required'],
            [['key'], 'string', 'max' => 16],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
            [['key'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeagues()
    {
        return $this->hasMany(League::className(), ['mode_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRankeds()
    {
        return $this->hasMany(Ranked::className(), ['mode_id' => 'id']);
    }
}

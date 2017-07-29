<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "regular".
 *
 * @property integer $id
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property RegularStage[] $regularStages
 * @property Stage[] $stages
 */
class Regular extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'regular';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'required'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['end_at'], 'unique'],
            [['start_at'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegularStages()
    {
        return $this->hasMany(RegularStage::className(), ['schedule_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(Stage::className(), ['id' => 'stage_id'])->viaTable('regular_stage', ['schedule_id' => 'id']);
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule_stage".
 *
 * @property integer $schedule_id
 * @property integer $stage_id
 *
 * @property Stage $stage
 * @property Schedule $schedule
 */
class ScheduleStage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'schedule_stage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['schedule_id', 'stage_id'], 'required'],
            [['schedule_id', 'stage_id'], 'integer'],
            [['schedule_id', 'stage_id'], 'unique', 'targetAttribute' => ['schedule_id', 'stage_id'], 'message' => 'The combination of Schedule ID and Stage ID has already been taken.'],
            [['stage_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stage::className(), 'targetAttribute' => ['stage_id' => 'id']],
            [['schedule_id'], 'exist', 'skipOnError' => true, 'targetClass' => Schedule::className(), 'targetAttribute' => ['schedule_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'schedule_id' => 'Schedule ID',
            'stage_id' => 'Stage ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStage()
    {
        return $this->hasOne(Stage::className(), ['id' => 'stage_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedule()
    {
        return $this->hasOne(Schedule::className(), ['id' => 'schedule_id']);
    }
}

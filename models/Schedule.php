<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "schedule".
 *
 * @property integer $id
 * @property integer $lobby_id
 * @property integer $mode_id
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Mode $mode
 * @property Lobby $lobby
 * @property ScheduleStage[] $scheduleStages
 * @property Stage[] $stages
 */
class Schedule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'schedule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lobby_id', 'mode_id', 'start_at', 'end_at', 'created_at', 'updated_at'], 'required'],
            [['lobby_id', 'mode_id', 'start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['end_at'], 'unique'],
            [['start_at'], 'unique'],
            [['mode_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mode::className(), 'targetAttribute' => ['mode_id' => 'id']],
            [['lobby_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lobby::className(), 'targetAttribute' => ['lobby_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lobby_id' => 'Lobby ID',
            'mode_id' => 'Mode ID',
            'start_at' => 'Start At',
            'end_at' => 'End At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMode()
    {
        return $this->hasOne(Mode::className(), ['id' => 'mode_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLobby()
    {
        return $this->hasOne(Lobby::className(), ['id' => 'lobby_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduleStages()
    {
        return $this->hasMany(ScheduleStage::className(), ['schedule_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(Stage::className(), ['id' => 'stage_id'])->viaTable('schedule_stage', ['schedule_id' => 'id']);
    }
}

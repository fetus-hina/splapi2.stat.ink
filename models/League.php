<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "league".
 *
 * @property integer $id
 * @property integer $mode_id
 * @property integer $start_at
 * @property integer $end_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Mode $mode
 * @property LeagueStage[] $leagueStages
 * @property Stage[] $stages
 */
class League extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'league';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mode_id', 'start_at', 'end_at', 'created_at', 'updated_at'], 'required'],
            [['mode_id', 'start_at', 'end_at', 'created_at', 'updated_at'], 'integer'],
            [['end_at'], 'unique'],
            [['start_at'], 'unique'],
            [['mode_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mode::className(), 'targetAttribute' => ['mode_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
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
    public function getLeagueStages()
    {
        return $this->hasMany(LeagueStage::className(), ['schedule_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStages()
    {
        return $this->hasMany(Stage::className(), ['id' => 'stage_id'])->viaTable('league_stage', ['schedule_id' => 'id']);
    }
}

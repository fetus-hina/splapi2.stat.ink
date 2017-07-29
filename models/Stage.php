<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stage".
 *
 * @property integer $id
 * @property string $key
 * @property string $name
 *
 * @property LeagueStage[] $leagueStages
 * @property League[] $schedules
 * @property RankedStage[] $rankedStages
 * @property Ranked[] $schedules0
 * @property RegularStage[] $regularStages
 * @property Regular[] $schedules1
 * @property SalmonStage[] $salmonStages
 * @property Salmon[] $schedules2
 */
class Stage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stage';
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
    public function getLeagueStages()
    {
        return $this->hasMany(LeagueStage::className(), ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules()
    {
        return $this->hasMany(League::className(), ['id' => 'schedule_id'])->viaTable('league_stage', ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRankedStages()
    {
        return $this->hasMany(RankedStage::className(), ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules0()
    {
        return $this->hasMany(Ranked::className(), ['id' => 'schedule_id'])->viaTable('ranked_stage', ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegularStages()
    {
        return $this->hasMany(RegularStage::className(), ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules1()
    {
        return $this->hasMany(Regular::className(), ['id' => 'schedule_id'])->viaTable('regular_stage', ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSalmonStages()
    {
        return $this->hasMany(SalmonStage::className(), ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules2()
    {
        return $this->hasMany(Salmon::className(), ['id' => 'schedule_id'])->viaTable('salmon_stage', ['stage_id' => 'id']);
    }
}

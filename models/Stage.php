<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "stage".
 *
 * @property integer $id
 * @property string $key
 * @property string $name
 * @property string $image_url
 * @property string $local_path
 *
 * @property ScheduleStage[] $scheduleStages
 * @property Schedule[] $schedules
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
            [['id'], 'required'],
            [['id'], 'integer'],
            [['key'], 'default',
                'value' => function () : ?string {
                    return static::getKeyById($this->id);
                },
            ],
            [['key'], 'string', 'max' => 32],
            [['name', 'local_path'], 'string', 'max' => 64],
            [['image_url'], 'string', 'max' => 256],
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
            'image_url' => 'Image Url',
            'local_path' => 'Local Path',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduleStages()
    {
        return $this->hasMany(ScheduleStage::className(), ['stage_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchedules()
    {
        return $this->hasMany(Schedule::className(), ['id' => 'schedule_id'])->viaTable('schedule_stage', ['stage_id' => 'id']);
    }

    public static function getKeyById(int $id) : ?string
    {
        $map = [
            0 => 'battera',
            1 => 'fujitsubo',
            2 => 'gangaze',
            3 => 'chozame',
            4 => 'ama',
            5 => 'kombu',
            7 => 'hokke',
            8 => 'tachiuo',
            9999 => 'mystery',
        ];
        return $map[$id] ?? null;
    }
}

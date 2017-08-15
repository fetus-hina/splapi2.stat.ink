<?php
namespace app\commands;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Yii;
use app\models\Ikaring2;
use yii\console\Controller;
use yii\helpers\FileHelper;

class Ikaring2Controller extends Controller
{
    const ISO8601_SHORT = 'Ymd\THisO';

    public $defaultAction = 'fetch';

    public function actionFetch()
    {
        $ikaring = Yii::createObject(Ikaring2::class);
        if (!$ikaring->login()) {
            return 1;
        }
        if (!$json = $ikaring->fetchSchedules()) {
            return 1;
        }
        $now = (new DateTimeImmutable())
            ->setTimestamp(time())
            ->setTimeZone(new DateTimeZone('Etc/UTC'));
        $this->saveJson('schedules', $json, $now);

        if (!$json = $ikaring->fetchStages()) {
            return 1;
        }
        $this->saveJson('stages', $json, $now);
    }

    private function saveJson(string $kind, string $json, DateTimeInterface $dateTime) : void
    {
        $path = implode(DIRECTORY_SEPARATOR, [
            Yii::getAlias('@app/database'),
            $kind,
            $dateTime->format('Ym'),
            $dateTime->format(static::ISO8601_SHORT) . '.json.gz'
        ]);
        FileHelper::createDirectory(dirname($path));
        file_put_contents($path, gzencode($json, 9, FORCE_GZIP));
    }
}

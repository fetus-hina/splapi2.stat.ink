<?php
namespace app\commands;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Yii;
use app\models\Ikaring2;
use app\models\Stage;
use jp3cki\uuid\Uuid;
use jp3cki\uuid\NS as UuidNS;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\Json;

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

        // ステージ情報の取得・整合
        if (!$json = $ikaring->fetchStages()) {
            return 1;
        }
        $this->importStages($ikaring, $json);

        // スケジュールの取得・登録
        if (!$json = $ikaring->fetchSchedules()) {
            return 1;
        }
        $now = (new DateTimeImmutable())
            ->setTimestamp(time())
            ->setTimeZone(new DateTimeZone('Etc/UTC'));
        $this->saveJson('schedules', $json, $now);
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

    private function importStages(Ikaring2 $ikaring, string $jsonString)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $stages = Json::decode($jsonString)['stages'];
        foreach ($stages as $json) {
            $this->importStage($ikaring, $json);
        }
        $transaction->commit();
    }

    private function importStage(Ikaring2 $ikaring, array $json) : bool
    {
        $model = Stage::findOne(['id' => $json['id']]);
        if (!$model) {
            $model = Yii::createObject([
                'class' => Stage::class,
                'id' => $json['id'],
                'name' => $json['name'],
                'image_url' => $this->prepareImageUrl((string)($json['image'] ?? '')),
            ]);
            if ($model->image_url) {
                $model->local_path = $this->downloadImage($ikaring, $model->image_url);
            }
        } else {
            $imageUrl = $this->prepareImageUrl((string)($json['image'] ?? ''));
            if ($imageUrl != $model->image_url) {
                if ($imageUrl) {
                    $model->local_path = $this->downloadImage($ikaring, $model->image_url);
                } else {
                    $model->local_path = null;
                }
            }
        }
        return $model->save();
    }

    private function prepareImageUrl(string $path) : ?string
    {
        if (substr($path, 0, 1) === '/') {
            return 'https://app.splatoon2.nintendo.net' . $path;
        }
        if (strpos($path, '://') !== false) {
            return $path;
        }
        return null;
    }

    private function downloadImage(Ikaring2 $ikaring, string $url) : ?string
    {
        $fileName = sprintf(
            '/images/%s.png',
            Uuid::v5(UuidNS::URL, $url)->formatAsString()
        );
        $localPath = Yii::getAlias('@app/web') . $fileName;
        if (file_exists($localPath) && is_file($localPath) && filesize($localPath) > 100) {
            return $fileName;
        }
        if (!$binary = $ikaring->downloadImage($url)) {
            return null;
        }
        FileHelper::createDirectory(dirname($localPath));
        file_put_contents($localPath, $binary);
        return $fileName;
    }
}

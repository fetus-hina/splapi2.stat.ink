<?php
namespace app\commands;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Yii;
use app\models\Ikaring2;
use app\models\Lobby;
use app\models\Mode;
use app\models\Schedule;
use app\models\ScheduleStage;
use app\models\Stage;
use jp3cki\uuid\NS as UuidNS;
use jp3cki\uuid\Uuid;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\Json;

class Ikaring2Controller extends Controller
{
    const ISO8601_SHORT = 'Ymd\THisO';

    public $defaultAction = 'fetch';

    public function actionFetch()
    {
        $time = (new DateTimeImmutable())->setTimeZone(new DateTimeZone('Etc/UTC'));

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
        $this->saveJson('schedules', $json, $time->setTimestamp(time()));
        //$json = file_get_contents(
        //    'compress.zlib://' . dirname(__DIR__) . '/database/schedules/201708/20170815T191140+0000.json.gz'
        //);
        $this->importSchedules($json);
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

    private function importStages(Ikaring2 $ikaring, string $jsonString) : bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        $stages = Json::decode($jsonString)['stages'];
        foreach ($stages as $json) {
            if (!$this->importStage($ikaring, $json)) {
                $transaction->rollback();
                return false;
            }
        }
        $transaction->commit();
        return true;
    }

    private function importStage(Ikaring2 $ikaring, array $json) : bool
    {
        // {{{
        $model = Stage::findOne(['id' => $json['id']]);
        if (!$model) {
            $model = Yii::createObject([
                'class' => Stage::class,
                'id' => $json['id'],
                'name' => $json['name'],
                'image_url' => $this->prepareImageUrl((string)($json['image'] ?? '')),
            ]);
            if ($model->image_url) {
                $model->local_path = $this->downloadImage($ikaring, 'stage', $model->image_url);
            }
        } else {
            $model->key = Stage::getKeyById($model->id);
            $imageUrl = $this->prepareImageUrl((string)($json['image'] ?? ''));
            if ($imageUrl != $model->image_url) {
                if ($imageUrl) {
                    $model->image_url = $imageUrl;
                    $model->local_path = $this->downloadImage($ikaring, 'stage', $model->image_url);
                } else {
                    $model->local_path = null;
                }
            }
        }
        $localPath = Yii::getAlias('@app/web') . '/' . $model->local_path;
        if ($localPath && file_exists($localPath)) {
            $webP = preg_replace('/\.(?:png|jpe?g)$/i', '.webp', $localPath);
            var_dump($webP);
            var_dump($webP != $localPath);
            var_dump(!file_exists($webP));
            if ($webP != $localPath && !file_exists($webP)) {
                $this->imageConvertToWebP($localPath, $webP);
            }
        }
        return $model->save();
        // }}}
    }

    private function importSchedules(string $jsonString) : bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        $json = Json::decode($jsonString);
        foreach (['regular', 'gachi', 'league'] as $key) {
            if (!$this->importModeSchedules($key, $json[$key])) {
                $transaction->rollback();
                return false;
            }
        }
        $transaction->commit();
        return true;
    }

    private function importModeSchedules(string $lobbyKey, array $json) : bool
    {
        if (!$lobby = Lobby::findOne(['key' => $lobbyKey])) {
            $this->stderr('Unknown lobby "' . $lobbyKey . "\"\n");
            return false;
        }

        usort($json, function (array $a, array $b) : int {
            return $a['start_time'] <=> $b['start_time'];
        });

        foreach ($json as $schedule) {
            if (!$this->importSchedule($lobby, $schedule)) {
                return false;
            }
        }
        return true;
    }

    private function importSchedule(Lobby $lobby, array $json) : bool
    {
        $ruleMap = [
            'turf_war' => 'nawabari',
            'splat_zones' => 'area',
            'tower_control' => 'yagura',
            'rainmaker' => 'hoko',
        ];
        $mode = Mode::findOne(['key' => $ruleMap[$json['rule']['key']]]);
        $schedule = Schedule::findOne([
            'lobby_id' => (int)$lobby->id,
            'start_at' => (int)$json['start_time'],
        ]);
        if (!$schedule) {
            $schedule = Yii::createObject(Schedule::class);
        }
        $schedule->attributes = [
            'lobby_id' => (int)$lobby->id,
            'mode_id' => (int)$mode->id,
            'start_at' => (int)$json['start_time'],
            'end_at' => (int)$json['end_time'],
        ];
        if ($schedule->isNewRecord || $schedule->dirtyAttributes) {
            if (!$schedule->save()) {
                $this->stderr("Schedule create/update failed.\n");
                foreach ($schedule->getFirstErrors() as $k => $v) {
                  $this->stderr("$k : $v\n");
                }
                return false;
            }
            $t = (new DateTimeImmutable())->setTimeZone(new DateTimeZone(Yii::$app->timeZone));
            $this->stderr(sprintf(
                "Created/updated schedule, id=%d, lobby=%s, mode=%s, %s/%s\n",
                $schedule->id,
                $lobby->name,
                $mode->name,
                $t->setTimestamp($schedule->start_at)->format(\DateTime::ATOM),
                $t->setTimestamp($schedule->end_at)->format(\DateTime::ATOM)
            ));
        } else {
            $t = (new DateTimeImmutable())->setTimeZone(new DateTimeZone(Yii::$app->timeZone));
            $this->stderr(sprintf(
                "Not changed schedule, id=%d, lobby=%s, mode=%s, %s/%s\n",
                $schedule->id,
                $lobby->name,
                $mode->name,
                $t->setTimestamp($schedule->start_at)->format(\DateTime::ATOM),
                $t->setTimestamp($schedule->end_at)->format(\DateTime::ATOM)
            ));
        }
        return $this->importScheduleStages(
            $schedule,
            [$json['stage_a'], $json['stage_b']]
        );
    }

    private function importScheduleStages(Schedule $schedule, array $stages) : bool
    {
        // すでに存在するレコードをチェックする
        $exists = $schedule->getStages()->count();
        if ($exists == 0) {
            goto register;
        }
        if ($exists != count($stages)) {
            // レコード数が違うので絶対おかしい
            ScheduleStage::deleteAll(['schedule_id' => $schedule->id]);
            goto register;
        }
        // ステージIDを使って引いてみて数が一致すれば正しい
        $checkCount = $schedule
            ->getStages()
            ->andWhere(['id' => array_map(
                function (array $a) : int {
                    return (int)$a['id'];
                },
                $stages
            )])
            ->count();
        if ($checkCount == $exists) {
            // 変更なし
            return true;
        }
        ScheduleStage::deleteAll(['schedule_id' => $schedule->id]);
        goto register;

        register:
        foreach ($stages as $stage) {
            if (!$stageModel = Stage::findOne(['id' => $stage['id']])) {
                $this->stderr("WARNING: stage id " . $stage['id'] . " does not exists.\n");
                continue;
            }
            $model = Yii::createObject([
                'class' => ScheduleStage::class,
                'schedule_id' => $schedule->id,
                'stage_id' => $stageModel->id,
            ]);
            if (!$model->save()) {
                $this->stderr(
                    "ScheduleStage register failed, schedule=" . $schedule->id . ", stage=" . $stage['id'] . "\n"
                );
                return false;
            }
            $this->stderr(sprintf(
                "  Created schedule-stage, schedule=%d, stage=%d/%s\n",
                $schedule->id,
                $stageModel->id,
                $stageModel->name
            ));
        }
        return true;
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

    private function downloadImage(Ikaring2 $ikaring, string $tag, string $url) : ?string
    {
        $fileName = sprintf(
            '/images/%s/%s.png',
            rawurlencode($tag),
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

    private function imageConvertToWebP(string $srcPath, string $dstPath) : bool
    {
        if (!is_file($srcPath) || !function_exists('imagewebp')) {
            return false;
        }
        if (!$gd = imagecreatefromstring(file_get_contents($srcPath))) {
            return false;
        }
        FileHelper::createDirectory(dirname($dstPath));
        imagesavealpha($gd, true);
        imagewebp($gd, $dstPath, 100);
        imagedestroy($gd);
        echo "WebP created\n";
        return true;
    }
}

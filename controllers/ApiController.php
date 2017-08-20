<?php
namespace app\controllers;

use Yii;
use app\models\Language;
use app\models\Lobby;
use app\models\Schedule;
use app\models\Stage;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

class ApiController extends Controller
{
    public function actionSchedule()
    {
        $resp = Yii::$app->response;
        $resp->format = 'json';
        return ArrayHelper::map(
            Lobby::find()->orderBy(['id' => SORT_ASC])->all(),
            'key',
            function (Lobby $lobby) : array {
                $q = $lobby->getSchedules()
                    ->andWhere(['>=', 'schedule.end_at', $_SERVER['REQUEST_TIME'] ?? time()])
                    ->orderBy(['schedule.end_at' => SORT_ASC])
                    ->with(['mode', 'stages']);
                return array_map(
                    function (Schedule $schedule) : array {
                        return [
                            'mode' => [
                                'key' => $schedule->mode->key,
                                'name' => static::nameFormat($schedule->mode->name),
                            ],
                            'start' => static::timeFormat($schedule->start_at),
                            'end' => static::timeFormat($schedule->end_at),
                            'stages' => array_map(
                                function (Stage $stage) : array {
                                    return [
                                        'splatnet' => $stage->id,
                                        'key' => $stage->key,
                                        'name' => static::nameFormat($stage->name),
                                        'image' => $stage->local_path
                                            ? Url::to('@web' . $stage->local_path, true)
                                            : null,
                                    ];
                                },
                                $schedule->stages
                            ),
                        ];
                    },
                    $q->all()
                );
            }
        );
    }

    public function actionStages()
    {
        $resp = Yii::$app->response;
        $resp->format = 'json';
        return array_map(
            function (Stage $stage) : array {
                return [
                    'splatnet' => $stage->id,
                    'key' => $stage->key,
                    'name' => static::nameFormat($stage->name),
                    'image' => $stage->local_path
                        ? Url::to('@web' . $stage->local_path, true)
                        : null,
                ];
            },
            Stage::find()->orderBy(['id' => SORT_ASC])->all()
        );
    }

    static public function timeFormat(int $time) : array
    {
        $t = (new \DateTimeImmutable())
            ->setTimeZone(new \DateTimeZone('Etc/UTC'))
            ->setTimestamp($time);
        return [
            'unixtime' => $t->getTimestamp(),
            'iso8601' => $t->format(\DateTime::ATOM),
        ];
    }

    static public function nameFormat(string $name) : array
    {
        static $langs;
        if ($langs === null) {
            $langs = Language::find()->orderBy(['code' => SORT_ASC])->asArray()->all();
        }
        $i18n = Yii::$app->i18n;
        return ArrayHelper::map(
            $langs,
            'code',
            function (array $lang) use ($name, $i18n) {
                return $i18n->translate('app', $name, [], $lang['code']);
            }
        );
    }
}

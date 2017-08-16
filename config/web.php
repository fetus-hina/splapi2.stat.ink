<?php
$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');
$config = [
    'id' => 'splapi2-stat-ink',
    'name' => 'SPLAPI2',
    'language' => 'ja-JP',
    'timeZone' => 'Asia/Tokyo',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => require(__DIR__ . '/cookie.php'),
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'ja-JP',
                    'fileMap' => [
                        'app' => 'app.php',
                    ]
                ],
            ],
        ],
        'assetManager' => [
            'assetMap' => [
                'bootstrap.css' => '@web/css/superhero.min.css',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => file_exists(__DIR__ . '/debug-ips.php')
            ? require(__DIR__ . '/debug-ips.php')
            : ['127.*', '::1', '192.168.*'],
    ];
}
return $config;

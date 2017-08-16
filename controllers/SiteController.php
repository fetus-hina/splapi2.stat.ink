<?php
namespace app\controllers;

use DirectoryIterator;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLicense()
    {
        $it = new DirectoryIterator(Yii::getAlias('@app/licenses'));
        $third = [];
        foreach ($it as $entry) {
            if ($entry->isDot() || !$entry->isFile()) {
                continue;
            }
            $basename = $entry->getBasename();
            $third[] = (object)[
                'name' => $basename,
                'html' => Html::tag(
                    'pre',
                    Html::encode(file_get_contents($entry->getPathname()))
                ),
            ];
        }
        usort(
            $third,
            function ($a, $b) {
                $aName = trim(preg_replace('/[^0-9A-Za-z]+/', ' ', $a->name));
                $bName = trim(preg_replace('/[^0-9A-Za-z]+/', ' ', $b->name));
                return strnatcasecmp($aName, $bName);
            }
        );
        return $this->render('license', [
            'third' => $third,
        ]);
    }
}

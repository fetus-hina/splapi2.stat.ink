<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
  <head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); echo "\n" ?>
  </head>
  <body>
<?php $this->beginBody() ?>
    <div class="wrap">
      <?php NavBar::begin([
          'brandLabel' => Yii::$app->name,
          'brandUrl' => Yii::$app->homeUrl,
          'options' => [
             'class' => 'navbar-inverse navbar-fixed-top',
          ],
      ]); echo "\n" ?>
      <?= Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
          ['label' => 'Home', 'url' => ['/site/index']],
          ['label' => 'SPLAPI 1', 'url' => 'https://splapi.fetus.jp/', 'options' => ['target' => '_blank']],
          ['label' => 'stat.ink', 'url' => 'https://stat.ink/'],
          ['label' => 'GitHub', 'url' => 'https://github.com/fetus-hina/splapi2.stat.ink'],
        ],
      ]) . "\n" ?>
      <?php NavBar::end(); echo "\n" ?>
      <div class="container">
        <?= Breadcrumbs::widget([
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) . "\n" ?>
        <?= $content . "\n" ?>
      </div>
    </div>
    <footer class="footer">
      <div class="container">
        <p class="pull-left">&copy; 2017 AIZAWA Hina</p>
        <p class="pull-right"><?= Yii::powered() ?></p>
      </div>
    </footer>
    <?php $this->endBody(); echo "\n" ?>
  </body>
</html>
<?php $this->endPage() ?>

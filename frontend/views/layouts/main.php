<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\Notification;
use kartik\widgets\Growl;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use frontend\components\Counter;
//use app\models\Notification;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
        <?php $this->registerLinkTag([
        'rel' => 'icon',
        'type' => 'image/x-icon',
        'href' => '/frontend/web/favicon.ico',
    ]);?>
    <?php $notifModel = Notification::find();
    $notifications = $notifModel->where(['id_user' => Yii::$app->user->id, 'active' => true]);
    $this->params['notifications'] = $notifications->all();
    $this->params['counter'] = $notifications->count(); ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="wrap">
<?php if (!Yii::$app->user->isGuest): ?>
    <div class="logo"></div>
<?php echo '<h1 class="titleMain">'.Html::encode($this->title).'</h1>' ?>
    <?= Counter::widget() ?>
<?php endif ?>
    <?php $counts = '<span class="glyphicon glyphicon-bell" style="font-size:21px"></span><span class="badge pull-right">'.$this->params['count'].'</span>'; ?>
    <?php
    // NavBar::begin([
    //     'brandLabel' => 'Holland',
    //     'brandUrl' => ['/zakaz/index'],
    //     'options' => [
    //         'class' => 'navbar-inverse navbar-fixed-top',
    //     ],
    // ]);
    // // $menuItems = [
    // //     ['label' => 'Home', 'url' => ['/site/index']],
    // //     ['label' => 'About', 'url' => ['/site/about']],
    // //     ['label' => 'Contact', 'url' => ['/site/contact']],
    // // ];
    // if (!Yii::$app->user->isGuest) {
    //     $menuItems[] = ['encode' => false, 'label' => $counts, 'options' => ['id' => 'notification']];
    // }
    // if (Yii::$app->user->isGuest) {
    //     $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
    // } else {
    //     $menuItems[] = '<li>'
    //         . Html::beginForm(['/site/logout'], 'post')
    //         . Html::submitButton(
    //             'Выйти (' . Yii::$app->user->identity->username . ')',
    //             ['class' => 'btn btn-link logout']
    //         )
    //         . Html::endForm()
    //         . '</li>';
    // }
    // echo Nav::widget([
    //     'options' => ['class' => 'navbar-nav navbar-right'],
    //     'items' => $menuItems,
    // ]);
    // NavBar::end();

    if (Yii::$app->user->isGuest) {
        echo '';
    } else {
        echo Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                '<span>'.Yii::$app->user->identity->name.'</span> <span class="glyphicon glyphicon-off exit"></span>',
                ['class' => 'btn btn-link logout', 'title' => 'Выход']
            )
            . Html::endForm();
    }
    ?>
    <?php if (!Yii::$app->user->isGuest): ?>
        <div class="notification-container hidden" id="notification-container">
            <div class="notification-content">
                <?php foreach($this->params['notifications'] as $notification){
                $date = date('Y-m-d H:i:s', time());
                    if ($notification->category == 0) {
                        $notif = '<span class="glyphicon glyphicon-road"></span> '.$notification->name.'<br>';
                    } elseif ($notification->category == 1) {
                        $notif = '<span class="glyphicon glyphicon-ok"></span> '.$notification->name.'<br>';
                    } elseif ($notification->category == 2) {
                        $notif = '<span class="glyphicon glyphicon-file"></span> '.$notification->name.'<br>';
                    } elseif ($notification->category == 4 && $notification->srok <= $date){
                        $notif = 'Напоминание о заказе №'.$notification->id_zakaz.' '.$notification->srok;
                    } elseif ($notification->category == 4 && $notification->srok >= $date){
                        $notif = '';
                    }
                    /** @var string $notif */
                    echo Html::a($notif.'<br>', ['notification/notification', 'id' => $notification->id_zakaz], ['id' => $notification->id_zakaz, 'class' => 'zakaz', 'data-key' => $notification->id_zakaz]);
                }
                ?>
            </div>
            <div class='notification-footer'>
            <?php echo Html::a('Прочитать все напоминание', ['notification/index']) ?>
            </div>
        </div>
    <?php endif ?>

<?php if (Yii::$app->user->isGuest): ?>
    <div class="headerLogin">
        <h1>HOLLAND <span>CRM 2.1</span></h1>
        <p>Управление заказами</p>
    </div>
<?php endif ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'homeLink' => ['label' => 'Главная', 'url' => ['zakaz/index']],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?php if (Yii::$app->session->hasFlash('update')) {
            echo Growl::widget([
                'type' => Growl::TYPE_SUCCESS,
                'body' => Yii::$app->session->removeFlash('update'),
            ]);
        } ?>
        <?php if (Yii::$app->session->hasFlash('errors')) {
            echo Growl::widget([
                'type' => Growl::TYPE_DANGER,
                'body' => Yii::$app->session->removeFlash('errors'),
            ]);
        } ?>
        <?= $content ?>
    </div>
</div>
<?php if (Yii::$app->user->isGuest): ?>
    <footer>
        <div class="footerLogin">
            <img src="img/logo.png" title="Logo">
            <div>Сеть магазинов</div>
            <div>&copy Holland <?php echo date('Y') ?></div>
        </div>
    </footer>
<?php endif ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Nav;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CustomSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Запросы';
?>
<div class="custom-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php echo Nav::widget([
		'options' => ['class' => 'nav nav-pills'],
		'items' => [
			['label' => 'Задачи', 'url' => ['todoist/shop']],
			['label' => 'Help Desk', 'url' => ['helpdesk/index']],
			['label' => 'Запросы на товар', 'url' => ['custom/index']],
		],
		]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
				'attribute' => 'date',
				'format' => ['datetime', 'dd.MM.Y H:m'],
			],
            [
				'attribute' => 'id_user',
				'value' => function($model){
					return $model->idUser->name;
				}
			],
            'tovar',
            'number',
            [
                'attribute' => '',
                'format' => 'raw',
                'value' => function($model) {
					if(Yii::$app->user->can('zakup')){
						return Html::a('Привезен', ['custom/close', 'id' => $model->id], ['class' => 'btn btn-primary btn-xs']);
					} else {
						return '';
					}
                },
            ],

            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
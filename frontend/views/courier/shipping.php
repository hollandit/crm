<?php

use app\models\Courier;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\CourierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Все доставки';
?>
<?php Pjax::begin(); ?>
<div class="courier-shipping">

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'floatHeader' => true,
        'headerRowOptions' => ['class' => 'headerTable'],
        'pjax' => true,
        'tableOptions' 	=> ['class' => 'table table-bordered tableSize'],
        'striped' => false,
        'rowOptions' => ['class' => 'trTable srok trNormal'],
        'columns' => [
            [
                'attribute' => 'date',
                'format' => ['date', 'php:d M'],
                'hAlign' => GridView::ALIGN_RIGHT,
                'contentOptions' => ['class' => 'border-left textTr tr70'],
            ],
            [
                'attribute' => 'commit',
                'contentOptions'=>['style'=>'white-space: normal;'],
            ],
            [
                'attribute' => 'to',
//                'hAlign' => GridView::ALIGN_RIGHT,
                'format' => 'raw',
                'value' => function($courier){
                    return '<span class="shipping">Откуда: </span>'.$courier->to ;
                },
                'contentOptions' => ['class' => 'textTr tr202'],
            ],
            [
                'attribute' => 'from',
//                'hAlign' => GridView::ALIGN_RIGHT,
                'format' => 'raw',
                'contentOptions' => ['class' => 'textTr tr202'],
                'value' => function($courier){
                    return '<span class="shipping">Куда: </span>'.$courier->from ;
                },
            ],
            [
                'attribute' => 'id_zakaz',
                'value' => 'idZakaz.prefics',
                'hAlign' => GridView::ALIGN_RIGHT,
                'contentOptions' => ['class' => 'textTr tr50', 'style' => 'border:none'],
            ],
            [
                'header' => '',
                'format' => 'raw',
                'value' => function($model){
                    /**
                     * Если курьер не взял доставку, то админ может в этом случае отменить
                     * в противном случае админ не сможет отменить */
                    if ($model->status == Courier::DOSTAVKA){
                        return Html::a('Удалить', ['deletes', 'id' => $model->id], [
                                'data' => ['confirm' => 'Вы действительно хотите удалить доставку?',
                                'method' => 'post']]);
                    } elseif($model->status == Courier::CANCEL){
                        return Html::encode('Отменен');
                    } else {
                        return '';
                    }
                },
                'contentOptions' => ['class' => 'border-right textTr tr50'],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?></div>

<?php

namespace frontend\controllers;

use app\models\Financy;
use app\models\Tag;
use app\models\Zakaz;
use app\models\ZakazTag;
use Yii;
use \yii\web\Controller;

class FinancyController extends Controller
{
    /**
     * Save payment order
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionDraft($id)
    {
        $model = Zakaz::findOne($id);
        $financy = new Financy();
        $financy->amount = $model->oplata - $model->fact_oplata;

        if ($financy->load(Yii::$app->request->post()) && $financy->validate()){
            $model->fact_oplata = $model->fact_oplata + $financy->sum;
            if ($model->oplata >= $model->fact_oplata){
                if ($model->oplata > $model->fact_oplata){
                    $model->save();
                    $financy->save();
                    Yii::$app->session->addFlash('update', 'Сумма зачлась '.$financy->sum.' руб.');
                    if (Yii::$app->user->can('admin')){
                        return $this->redirect(['zakaz/admin', '#' => $id]);
                    } else {
                        return $this->redirect(['zakaz/shop']);
                    }
                } else {
                    if ($model->status == Zakaz::STATUS_EXECUTE){
                        $model->action = 0;
                        Yii::$app->session->addFlash('update', 'Заказ закрылся');
                    }
                    /* Соранение тега оплачено*/
                    $tag = new ZakazTag();
                    $tag->financy($model->id_zakaz);
                    $model->save();
                    Yii::$app->session->addFlash('update', ' Сумма зачлась '.$financy->sum.' руб.');
                    if (Yii::$app->user->can('admin')){
                        return $this->redirect(['zakaz/admin', 'id' => $id]);
                    } else {
                        return $this->redirect(['zakaz/shop']);
                    }
                }
            }
        }

        return $this->render('draft', [
            'financy' => $financy,
            'model' => $model,
        ]);
    }
}

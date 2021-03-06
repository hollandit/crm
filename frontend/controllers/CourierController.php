<?php

namespace frontend\controllers;

use Yii;
use app\models\Courier;
use app\models\User;
use app\models\Notification;
use app\models\CourierSearch;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * CourierController implements the CRUD actions for Courier model.
 */
class CourierController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                // 'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['courier', 'program'],
                    ],
                    [
                        'actions' => ['ready'],
                        'allow' => true,
                        'roles' => ['courier', 'program'],
                    ],
                    [
                        'actions' => ['make'],
                        'allow' => true,
                        'roles' => ['courier', 'program'],
                    ],
                    [
                        'actions' => ['delivered'],
                        'allow' => true,
                        'roles' => ['courier', 'program'],
                    ],
                    [
                        'actions' => ['shipping'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['deletes'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Courier models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourierSearch();
        $notification = $this->findNotification();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notification' => $notification,
        ]);
    }
    public function actionReady()
    {
        $courier = Courier::find();
        $notification = $this->findNotification();
        $dataProvider = new ActiveDataProvider([
            'query' => $courier->andWhere(['>', 'data_from', '0000-00-00 00:00:00']),
            'pagination' => ['pageSize' => 50,]
        ]);

        return $this->render('ready', [
            'dataProvider' => $dataProvider,
            'notification' => $notification,
        ]);
    }
    /** View for admin scans all active shipping */
    public function actionShipping()
    {
        $courier = Courier::find();
        $searchModel = new CourierSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => $courier->where(['status' => Courier::DOSTAVKA]),
            'pagination' => ['pageSize' => 50,]
        ]);
        $notification = $this->findNotification();//Уведомление

        return $this->render('shipping', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'notification' => $notification,
        ]);
    }

    /**
     * Delete shipping after courier not accepted shipping
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDeletes($id)
    {
        $model =  $this->findModel($id);
        $user = User::findOne(['id' => User::USER_COURIER]);
        $model->status = Courier::CANCEL;
        if(!$model->save()){
            print_r($model->getErrors());
            Yii::$app->session->addFlash('errors', 'Произошла ошибка!');
        } else {
            $model->save();
            Yii::$app->session->addFlash('update', 'Доставка былаа отклонена');
            try{
                \Yii::$app->bot->sendMessage($user->telegram_chat_id, 'Отменена доставка '.$model->idZakaz->prefics);
            }catch (Exception $e){
                $e->getMessage();
            }
        }

        return $this->redirect(['shipping']);
    }

    /**
     * Displays a single Courier model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Courier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Courier();
        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true])->all();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()){
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                print_r($model->getErrors());
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Courier model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true])->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Courier model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    public function actionMake($id)//Курьер забрал заказ
    {
        $model = $this->findModel($id);
        $notification = new Notification();
        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true])->all();

        $model->data_to = date('Y-m-d H:i:s');
        $model->status = Courier::RECEIVE;

        $notification->getByIdNotification(5, $model->id_zakaz);//Уведомление, что курьер забрал доставку
        $notification->saveNotification;

        if ($model->save()){
            return $this->redirect(['index']);
        } else {
            print_r($model->getErrors());
        }
    }
    public function actionDelivered($id)//Курьер доставил заказ
    {
        $model = $this->findModel($id);
        $notification = new Notification();
        $model->data_from = date('Y-m-d H:i:s');
        $model->status = Courier::DELIVERED;
        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true])->all();

        $notification->getByIdNotification(8, $model->id_zakaz);//Уведомление, что курьер доставил доставку
        $notification->saveNotification;

        if ($model->save()){
            return $this->redirect(['index']);
        } else {
            print_r($model->getErrors());
        }
    }
    /**
     * Finds the Courier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Courier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Courier::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    protected function findNotification()
    {
        $notification = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true]);
        if($notification->count()>50){
            $notifications = '50+';
        } elseif ($notification->count()<1){
            $notifications = '';
        } else {
            $notifications = $notification->count();
        }

        $this->view->params['notifications'] = $notification->all();
        $this->view->params['count'] =  $notifications;
    }
}

<?php

namespace frontend\controllers;

use Yii;
use app\models\Custom;
use app\models\CustomSearch;
use app\models\Notification;
use yii\base\Model;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;


/**
 * CustomController implements the CRUD actions for Custom model.
 */
class CustomController extends Controller
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
				'rules' => [
					[
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['zakup', 'program'],
					],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['shop', 'program'],
                    ],
                    [
                        'actions' => ['close'],
                        'allow' => true,
                        'roles' => ['zakup', 'program'],
                    ],
                    [
                        'actions' => ['brought'],
                        'allow' => true,
                        'roles' => ['seeAdop'],
                    ],
					[
                        'actions' => ['adop'],
                        'allow' => true,
                        'roles' => ['seeAdop'],
					],
				],
			],
        ];
    }

    /**
     * Lists all Custom models for role zakup.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'zakup');
        $notification = $this->findNotification();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	/**
     * Lists all Custom models for shop.
     * @return mixed
     */
    public function actionAdop()
    {
        $searchModel = new CustomSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'adop');
        $notification = $this->findNotification();

        return $this->render('adop', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Custom model.
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
     * Creates a new Custom model.
     * If creation is successful, the browser will be redirected to the 'shop' page.
     * Class TabularInputAction
     * @param unclead\multipleinput\examples\actions
     * @return mixed
     */
    public function actionCreate()
    {
        $notification = $this->findNotification();
        $models = [new Custom()];
        $request = Yii::$app->getRequest();
        if ($request->isPost && $request->post('ajax') !== null){
            $data = Yii::$app->request->post('Custom', []);
            foreach (array_keys($data) as $index) {
                $models[$index] = new Custom();
            }
            Model::loadMultiple($models, Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            $result = ActiveForm::validateMultiple($models);
            return $result;
        }

        if(Model::loadMultiple($models, Yii::$app->request->post())) {
            foreach ($models as $custom) {
                if ($custom->save()){
                    return $this->redirect(Yii::$app->request->referrer);
                } else{
                   print_r($custom->getErrors());
                }
            }
        } else {
            return $this->render('create', [
               'models' => $models,
            ]);
        }
    }


    /**
     * Updates an existing Custom model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Custom model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    /**
     * notification role zakup about brought custom
     */
    public function actionBrought($id){
        $model = $this->findModel($id);
        $model->action = 2;
        if ($model->save()){
            return $this->redirect(['custom/adop']);
        } else {
            print_r($model->getErrors());
        }

    }
    /**
     * Close an existing Custom model.
     * if close is successful, the browser will be redirected to hte 'index' page.
     * @param $id
     * @return Response
     */
    public function actionClose($id)
    {
        $notification = $this->findNotification();
        
        $model = $this->findModel($id);
        $model->action = 1;
        $model->date_end = date('Y-m-d H:i:s');
        $model->save();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Custom model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Custom the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Custom::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Проверяет активные уведомлеине у пользователя.
     * Если кол-во уведомлении больше 50, то выводит 50+
     */
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

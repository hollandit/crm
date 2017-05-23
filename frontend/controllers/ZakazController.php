<?php

namespace frontend\controllers;

use Yii;
use app\models\Zakaz;
use app\models\Courier;
use app\models\Notification;
use app\models\ZakazSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\rbac\AuthorRule;
use console\controllers\RbacController;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
/**
 * ZakazController implements the CRUD actions for Zakaz model.
 */
class ZakazController extends Controller
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
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['shop', 'admin', 'program'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['admin', 'disain', 'master', 'program', 'shop'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['admin', 'disain', 'master', 'program', 'shop'],
                    ],
                    [
                        'actions' => ['check'],
                        'allow' => true,
                        'roles' => ['master', 'program'],
                    ],
                    [
                        'actions' => ['close'],
                        'allow' => ture,
                        'roles' => ['admin', 'program', 'shop'],
                    ],
                    [
                        'actions' => ['restore'],
                        'allow' => ture,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['admin'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['shop'],
                        'allow' => true,
                        'roles' => ['shop', 'program'],
                    ],
                    [
                        'actions' => ['disain'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                    [
                        'actions' => ['master'],
                        'allow' => true,
                        'roles' => ['master', 'program'],
                    ],
                    [
                        'actions' => ['courier'],
                        'allow' => true,
                        'roles' => ['courier', 'program'],
                    ],
                    [
                        'actions' => ['archive'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['closezakaz'],
                        'allow' => true,
                        'roles' => ['shop', 'program'],
                    ],
                    [
                        'actions' => ['ready'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                    [
                        'actions' => ['adopted'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['statusdisain'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Zakaz models.
     * @return mixed
     */
    public function actionIndex()
    {
        $notification = $this->findNotification();

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single Zakaz model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		Yii::$app->nodeSocket;
        $model = $this->findModel($id);
        $notification = new Notification();
        $shipping = new Courier();
        $reminder = new Notification();
        $zakaz = $model->id_zakaz;
        $notification = $this->findNotification();
        

        if ($shipping->load(Yii::$app->request->post())) {
            $shipping->save();
            $model->id_shipping = $shipping->id;//Оформление доставки
            $model->save();

            $notification->getByIdNotification(7, $zakaz);
            $notification->saveNotification;
            
            return $this->redirect(['view', 'id' => $model->id_zakaz]);
        }
        
        if($reminder->load(Yii::$app->request->post())){
            $reminder->getReminder($zakaz);
            if($reminder->validate() && $reminder->save()){
                Yii::$app->session->setFlash('success', 'Напоминание было создана');
            } else {
                Yii::$app->session->setFlash('error', 'Извините. Напоминание не было создана');
            }
            unset($reminder->srok);
            return $this->redirect(['view', 'id' => $model->id_zakaz]);
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->uploadeFile;//Выполнение работы дизайнером и оформление уведомление
            $model->validate();
            $model->save();
            
            if ($model->status == 3) {
                $notification->getByIdNotification(3, $zakaz);
                $notification->saveNotification;
            } elseif ($model->status == 6) {
                $notification->getByIdNotification(6, $zakaz);
                $notification->saveNotification;
            }

            return $this->redirect(['view', 'id' => $model->id_zakaz]);
        }
        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->getId(), 'active' => true])->all();

        return $this->render('view', [
            'model' => $this->findModel($id),
            'notification' => $notification,
            'user_name' => $user_name,
            'shipping' => $shipping,
            'file' => $file,
            'reminder' => $reminder,
        ]);
    }
    
    /**
     * Creates a new Zakaz model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Zakaz();
        $notification = $this->findNotification();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if($model->upload()){
            $model->img = time().'.'.$model->file->extension;
            }
            $model->save();

            if (Yii::$app->user->can('shop')) {
                return $this->redirect(['shop']);
            } elseif (Yii::$app->user->can('admin')) {
               return $this->redirect(['admin']);
           }
        } else {
            return $this->render('create', [
                'model' => $model,
                'url' => $url,
            ]);
        }
    }

    /**
     * Updates an existing Zakaz model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $notification = $this->findNotification();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if(isset($model->file))
            {
            $model->file->saveAs('attachment/'.$model->id_zakaz.'.'.$model->file->extension);
            $model->img = $model->id_zakaz.'.'.$model->file->extension;
            }
            if ($model->status == 3) {
                $model->data_start_disain = date('Y-m-d H:i:s');
            }
            
            $model->save();

            if (Yii::$app->user->can('shop')) {
                return $this->redirect(['shop']);
            } elseif (Yii::$app->user->can('admin')) {
               return $this->redirect(['admin']);
           }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    public function actionCheck($id)//Мастер выполнил свою работу
    {
        $model = $this->findModel($id);
        $notification = new Notification();
        $notification = $this->findNotification();

        $model->status = 7;
        $notification->getByIdNotification(8, $id);
        $notification->saveNotification;
        $model->save();
        
        return $this->redirect(['zakaz/master']);
    }
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->action = 0;
        $model->save();

        $this->view->params['notifications'] = Notification::find()->where(['id_user' => Yii::$app->user->id, 'active' => true])->all();

        if (Yii::$app->user->can('shop')) {
                return $this->redirect(['shop']);
            } elseif (Yii::$app->user->can('admin')) {
               return $this->redirect(['admin']);
           }
    }
    public function actionRestore($id)
    {
        $notification = $this->findNotification();

        $model = $this->findModel($id);
        $model->action = 1;
        $model->save();

        return $this->redirect(['archive']);
    }
    public function actionAdopted($id)
    {
        $notification = $this->findNotification();

        $model = $this->findModel($id);
        $model->status = 2;
        $model->save();

        return $this->redirect(['view', 'id' => $model->id_zakaz]);
    }
    public function actionArchive()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'archive');
        $notification = $this->findNotification();

        return $this->render('archive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionClosezakaz()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'closeshop');
        $notification = $this->findNotification();

        return $this->render('closezakaz', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionReady()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = new ActiveDataProvider([
                'query' => Zakaz::find()->andWhere(['status' => Zakaz::STATUS_SUC_DISAIN, 'action' => 1]),
                'sort' => ['defaultOrder' => ['srok' => SORT_DESC]] 
            ]);
        $notification = $this->findNotification();

        return $this->render('ready', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            ]);
    }

    //Disain
    public function actionStatusdisain($id)
    {
        $notification = $this->findNotification();

        $model = $this->findModel($id);
        $model->statusDisain = 1;
        $model->save();

        return $this->redirect(['view', 'id' => $model->id_zakaz]);
    }

    /**
     * Deletes an existing Zakaz model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionShop()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'shop');
        $notification = $this->findNotification();

        return $this->render('shop', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionDisain()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'disain');
        $notification = $this->findNotification();

        return $this->render('disain', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionMaster()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'master');
        $notification = $this->findNotification();

        return $this->render('master', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionAdmin()
    {
        $notification = $this->findNotification();
        
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'admin');
        $image = $model->img;

        $dataProviderNew = $searchModel->search(Yii::$app->request->queryParams, 'adminNew');
        $dataProviderWork = $searchModel->search(Yii::$app->request->queryParams, 'adminWork');
        $dataProviderIspol = $searchModel->search(Yii::$app->request->queryParams, 'adminIspol');

        return $this->render('admin', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderNew' => $dataProviderNew,
            'dataProviderWork' => $dataProviderWork,
            'dataProviderIspol' => $dataProviderIspol,
            'image' => $image,
        ]);
    }

    /**
     * Finds the Zakaz model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Zakaz the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Zakaz::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    protected function findShipping($id)
    {
        if (($shipping = Courier::findOne($id)) !== null) {
            return $shipping;
        } else {
            throw new NotFoundHttpException("The requested page does not exist.");
            
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

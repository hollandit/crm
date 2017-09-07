<?php

namespace frontend\controllers;

use app\models\Client;
use app\models\Financy;
use app\models\User;
use app\models\ZakazTag;
use frontend\models\Telegram;
use Yii;
use app\models\Zakaz;
use app\models\Courier;
use app\models\Comment;
use app\models\Notification;
use app\models\ZakazSearch;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
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
                        'roles' => ['admin', 'disain', 'master', 'program', 'shop', 'zakup'],
                    ],
                    [
                        'actions' => ['check'],
                        'allow' => true,
                        'roles' => ['master', 'program'],
                    ],
                    [
                        'actions' => ['uploadedisain'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                    [
                        'actions' => ['close'],
                        'allow' => true,
                        'roles' => ['admin', 'program', 'shop'],
                    ],
                    [
                        'actions' => ['restore'],
                        'allow' => true,
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
                        'actions' => ['adopdisain'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                    [
                        'actions' => ['adopmaster'],
                        'allow' => true,
                        'roles' => ['master', 'program'],
                    ],
                    [
                        'actions' => ['statusdisain'],
                        'allow' => true,
                        'roles' => ['disain', 'program'],
                    ],
                    [
                        'actions' => ['zakazedit'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['zakaz'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['comment'],
                        'allow' => true,
                        'roles' => ['admin', 'program'],
                    ],
                    [
                        'actions' => ['declined'],
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                    [
                        'actions' => ['accept'],
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                    [
                        'actions' => ['refusing'],
                        'allow' => true,
                        'roles' => ['seeAdop']
                    ],
                    [
                        'actions' => ['fulfilled'],
                        'allow' => true,
                        'roles' => ['admin']
                    ],
                    [
                        'actions' => ['reconcilation'],
                        'allow' => true,
                        'roles' => ['disain']
                    ],
                    [
                        'actions' => ['renouncement'],
                        'allow' => true,
                        'roles' => ['shop', 'admin']
                    ]
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
        $model = new Zakaz();

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
        $model = $this->findModel($id);
        $reminder = new Notification();
        $zakaz = $model->id_zakaz;

        if ($reminder->load(Yii::$app->request->post())) {
            $reminder->getReminder($zakaz);
            if ($reminder->validate() && $reminder->save()) {
                Yii::$app->session->setFlash('success', 'Напоминание было создана');
            } else {
                Yii::$app->session->setFlash('error', 'Извините. Напоминание не было создана');
            }
            unset($reminder->srok);
            return $this->redirect(['view', 'id' => $model->id_zakaz]);
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
            'reminder' => $reminder,
        ]);
    }

    /**
     * appointed shipping in courier
     * @param $id
     * @return string
     */
    public function actionShipping($id)
    {
        $model = $this->findModel($id);
        $shipping = new Courier();
        $user = User::findOne(['id' => User::USER_ADMIN]);
        if ($model->load(Yii::$app->request->post())) {
            $shipping->save();
            $model->id_shipping = $shipping->id;
            if ($model->save()){
                try{
                    \Yii::$app->bot->sendMessage($user->telegram_chat_id, 'Назначена доставка '.$model->prefics);
                }catch (Exception $e){
                    $e->getMessage();
                }
                Yii::$app->session->addFlash('update', 'Успешно создана доставка');
            } else {
                $this->flashErrors($id);
            }
        }

        return $this->render('shipping', [
            'model' => $model,
            'shipping' => $shipping,
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
        $client = new Client();
        $client->scenario = Client::SCENARIO_CREATE;
        $telegram = new Telegram();
        $financy = new Financy();

        if ($model->load(Yii::$app->request->post()) && $client->load(Yii::$app->request->post())) {
            if (Yii::$app->request->get('id')){
                $model->id_client = ArrayHelper::getValue(Yii::$app->request->get(), 'id');
            } else {
                $model->id_client = ArrayHelper::getValue(Yii::$app->request->post('Client'), 'id');
            }
            $model->id_shop = $model->id_sotrud;
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->file) {
                $model->upload('create');
            }
            if ($model->status == Zakaz::STATUS_DISAIN or $model->status == Zakaz::STATUS_MASTER or $model->status == Zakaz::STATUS_AUTSORS) {
                if ($model->status == Zakaz::STATUS_DISAIN) {
                    $model->unread(null, 'new', 'disain',0);
                } elseif ($model->status == Zakaz::STATUS_MASTER) {
                    $model->unread(null, 'new', 'master',0);
                } else {
                    $model->id_unread = 0;
                }
            }
            if ($model->validate() && $client->validate()){
                if (!$model->save()) {
                    $this->flashErrors();
                } else {
                    $financy->saveSum($model->fact_oplata, $model->id_zakaz, $model->oplata);
                    Yii::$app->session->addFlash('update', 'Успешно создан заказ');
                        if($model->status == Zakaz::STATUS_DISAIN){
                                $telegram->message(User::USER_DISAYNER, 'Назначен заказ '.$model->prefics.' '.$model->description);
                        }
                            $telegram->message(User::USER_ADMIN, 'Создан заказ '.$model->prefics.' '.$model->description);
                }

                if (Yii::$app->user->can('shop')) {
                    return $this->redirect(['shop']);
                } elseif (Yii::$app->user->can('admin')) {
                    return $this->redirect(['admin']);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'client' => $client,
        ]);
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
        $client = new Client();
        $client->scenario = Client::SCENARIO_CREATE;
        $user = User::findOne(['id' => User::USER_DISAYNER]);
        $telegram = new Telegram();

        if ($model->load(Yii::$app->request->post()) && $client->load(Yii::$app->request->post())) {
            $model->id_client = ArrayHelper::getValue(Yii::$app->request->post('Client'), 'id');
            $model->file = UploadedFile::getInstance($model, 'file');
            if (isset($model->file)) {
                $model->upload('update', $id);
            }
            if ($model->status == Zakaz::STATUS_DISAIN or $model->status == Zakaz::STATUS_MASTER or Zakaz::STATUS_AUTSORS) {
                if ($model->status == Zakaz::STATUS_DISAIN) {
                    $model->unread(null, 'new', 'disain',0);
                } elseif ($model->status == Zakaz::STATUS_MASTER) {
                    $model->unread(null, 'new', 'master',0);
                } else {
                    $model->id_unread = 0;
                }
            }
            if ($model->validate() && $client->validate()){
                if (!$model->save()) {
                    $this->flashErrors($id);
                } else {
                    $arr = ArrayHelper::map($model->tags, 'id', 'id');
                    if (Yii::$app->request->post('Zakaz')['tags_array']){
                        foreach (Yii::$app->request->post('Zakaz')['tags_array'] as $one){
                            if (!in_array($one, $arr)){
                                $tag = new ZakazTag();
                                $tag->zakaz_id = $id;
                                $tag->tag_id = $one;
                                $tag->save();
                            }
                            if (isset($arr[$one])){
                                unset($arr[$one]);
                            }
                        }
                        ZakazTag::deleteAll(['tag_id' => $arr]);
                    }
                    if($model->status == Zakaz::STATUS_DISAIN && $user->telegram_chat_id){
                        $telegram->message(User::USER_DISAYNER, 'Назначен заказ '.$model->prefics.' '.$model->description);
                    }
                    Yii::$app->session->addFlash('update', 'Успешно отредактирован заказ');
                }

                if (Yii::$app->user->can('shop')) {
                    return $this->redirect(['shop']);
                } elseif (Yii::$app->user->can('admin')) {
                    return $this->redirect(['admin']);
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'client' => $client,
        ]);
    }

    /**
     * Master fulfilled zakaz
     * if success redirected zakaz/master
     * @param $id
     * @return \yii\web\Response
     */
    public function actionCheck($id)//Мастер выполнил свою работу
    {
        $model = $this->findModel($id);
        $notification = new Notification();
        $telegram = new Telegram();

        $model->unread('suc', 'suc', 'master',true);
        $notification->getByIdNotification(8, $id);
        $notification->saveNotification;
        if ($model->save()) {
            $telegram->message(User::USER_ADMIN, 'Мастер выполнил работу '.$model->prefics.' '.$model->description);
            Yii::$app->session->addFlash('update', 'Заказ отправлен на проверку');
            return $this->redirect(['master']);
        } else {
            $this->flashErrors($id);
        }
    }

    /**
     * Disain filfilled zakaz
     * @param $id
     * @return string
     */
    public function actionUploadedisain($id)
    {
        $model = $this->findModel($id);
        $telegram = new Telegram();

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');
            //Выполнение работы дизайнером
            if (isset($model->file)) {
                $model->uploadeFile;
            }
            $model->unread('suc', 'suc', 'disain',true);
            if ($model->save()) {
                Yii::$app->session->addFlash('update', 'Заказ отправлен на проверку');
                $telegram->message(User::USER_ADMIN, 'Дизайнер выполнил работу '.$model->prefics.' '.$model->description);
                return $this->redirect(['disain', 'id' => $id]);
            } else {
                $this->flashErrors($id);
            }
        }
        return $this->renderAjax('_upload', [
            'model' => $model
        ]);
    }

    /**
     * When zakaz close Shope or Admin
     * if success then redirected shop or admin
     * @param integer $id
     * @return mixed
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->action = 0;
        if (!$model->save()) {
            $this->flashErrors($id);
        } else {
            $model->save();
            Yii::$app->session->addFlash('update', 'Заказ успешно закрылся');
        }

        if (Yii::$app->user->can('shop')) {
            return $this->redirect(['shop']);
        } elseif (Yii::$app->user->can('admin')) {
            return $this->redirect(['admin']);
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     */
    public function actionRestore($id)
    {
        $model = $this->findModel($id);
        $model->action = 1;
        $model->save();
        Yii::$app->session->addFlash('update', 'Заказ успешно активирован');

        return $this->redirect(['archive']);
    }

    /**
     * New zakaz become in status adopted
     * @param $id
     * @return \yii\web\Response
     */
    public function actionAdopted($id)
    {
        $model = $this->findModel($id);
        $model->status = Zakaz::STATUS_ADOPTED;
        $model->save();
    }

    /**
     * New zakaz become in status wokr for disain
     * @param $id
     * @return \yii\web\Response
     */
    public function actionAdopdisain($id)
    {
        $model = $this->findModel($id);
        $model->statusDisain = Zakaz::STATUS_DISAINER_WORK;
        $model->save();
    }

    /**
     * New zakaz become in status wokr for master
     * @param $id
     * @return \yii\web\Response
     */
    public function actionAdopmaster($id)
    {
        $model = $this->findModel($id);
        $model->statusMaster = Zakaz::STATUS_MASTER_WORK;
        $model->save();
    }

    /**
     * Zakaz fulfilled
     * if success then redirected zakaz/admin
     * @param $id
     * @return \yii\web\Response
     */
    public function actionFulfilled($id)
    {
        $model = $this->findModel($id);
        $model->unread('execute', null, null,0);
        if ($model->save()) {
            Yii::$app->session->addFlash('update', 'Выполнен заказ №'.$model->prefics);
            return $this->redirect(['admin']);
        } else {
            $this->flashErrors($id);
        }
    }

    /**
     * Zakaz the disainer
     * if success then redirected zakaz/disain
     * @param $id
     * @return \yii\web\Response
     */
    public function actionReconcilation($id)
    {
        $model = $this->findModel($id);

        if ($model->statusDisain == Zakaz::STATUS_DISAINER_SOGLAS) {
            $model->statusDisain = Zakaz::STATUS_DISAINER_WORK;
        } else {
            $model->statusDisain = Zakaz::STATUS_DISAINER_SOGLAS;
        }
        if ($model->save()) {
            return $this->redirect(['disain']);
        } else {
            $this->flashErrors($id);
        }
    }

    /**
     * All existing close zakaz in Admin
     * @return string
     */
    public function actionArchive()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'archive');

        return $this->render('archive', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** All close zakaz in shop */
    public function actionClosezakaz()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'closeshop');

        return $this->render('closezakaz', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /** All fulfilled disain */
    public function actionReady()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = new ActiveDataProvider([
            'query' => Zakaz::find()->andWhere(['status' => Zakaz::STATUS_SUC_DISAIN, 'action' => 1]),
            'sort' => ['defaultOrder' => ['srok' => SORT_DESC]]
        ]);

        return $this->render('ready', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Disain internal status zakaz
     * @param $id
     * @return \yii\web\Response
     */
    public function actionStatusdisain($id)
    {
        $model = $this->findModel($id);
        $model->statusDisain = Zakaz::STATUS_DISAINER_WORK;
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

    public function actionRenouncement($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()){
            if (!$model->save()){
                print_r($model->getErrors());
            }
            Yii::$app->mailer->compose()
                ->setFrom('holland.itkzn@gmail.com')
                ->setTo('holland.itkzn@gmail.com')
                ->setSubject('Отказ от клиента')
                ->setTextBody($model->prefics.' '.$model->renouncement)
                ->send();
            if (Yii::$app->user->can('shop')){
                return $this->redirect(['shop']);
            } else {
                return $this->redirect(['admin']);
            }

        }
    }
    /** START view role */
    /**
     * All zakaz existing in Shop
     * @return string
     */
    public function actionShop()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'shopWork');
        $dataProviderExecute = $searchModel->search(Yii::$app->request->queryParams, 'shopExecute');

        return $this->render('shop', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderExecute' => $dataProviderExecute,
        ]);
    }

    /**
     * All zakaz existing in Disain
     * @return string
     */
    public function actionDisain()
    {
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'disain');
        $dataProviderSoglas = $searchModel->search(Yii::$app->request->queryParams, 'disainSoglas');

        return $this->render('disain', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderSoglas' => $dataProviderSoglas,
        ]);
    }

    /**
     * All zakaz existing in Master
     * @return string
     */
    public function actionMaster()
    {
        $comment = new Comment();
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'master');
        $dataProviderSoglas = $searchModel->search(Yii::$app->request->queryParams, 'masterSoglas');

        return $this->render('master', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderSoglas' => $dataProviderSoglas,
            'comment' => $comment,
        ]);
    }

    /**
     * All zakaz existing in Admin
     * @return string|\yii\web\Response
     * windows Admin
     */
    public function actionAdmin()
    {
        $notifications = new Notification();
        $model = new Zakaz();
        $comment = new Comment();
        $shipping = new Courier();
        $telegram = new Telegram();

        if ($comment->load(Yii::$app->request->post())) {
            if ($comment->save()) {
                return $this->redirect(['admin']);
            } else {
                $this->flashErrors();
            }
        }

        if ($shipping->load(Yii::$app->request->post())) {
            $shipping->save();//сохранение доставка
            if (!$shipping->save()) {
                $this->flashErrors();
            }
            $model = Zakaz::findOne($shipping->id_zakaz);//Определяю заказ
            $model->id_shipping = $shipping->id;//Оформление доставку в таблице заказа
            if ($model->save()){
                /** @var $model \app\models\Zakaz */
                Yii::$app->session->addFlash('update', 'Доставка успешно создана');
                $telegram->message(User::USER_ADMIN, 'Назначена доставка '.$model->prefics);
            } else {
                $this->flashErrors();
            }

            $notifications->getByIdNotification(7, $shipping->id_zakaz);//оформление уведомлений
            $notifications->saveNotification;

            return $this->redirect(['admin', '#' => $model->id_zakaz]);
        }

        $image = $model->img;
        $searchModel = new ZakazSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 'admin');
        $dataProviderNew = $searchModel->search(Yii::$app->request->queryParams, 'adminNew');
        $dataProviderWork = $searchModel->search(Yii::$app->request->queryParams, 'adminWork');
        $dataProviderIspol = $searchModel->search(Yii::$app->request->queryParams, 'adminIspol');
        $dataProvider->pagination = false;

        return $this->render('admin', [
            'comment' => $comment,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderNew' => $dataProviderNew,
            'dataProviderWork' => $dataProviderWork,
            'dataProviderIspol' => $dataProviderIspol,
            'image' => $image,
        ]);
    }
    /** END view role */
    /** START Block admin in gridview */
    /**
     * Zakaz deckined admin and in db setup STATUS_DECLINED_DISAIN or STATUS_DECLINED_MASTER
     * if success then redirected view admin
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionDeclined($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Zakaz::SCENARIO_DECLINED;
        $telegram = new Telegram();
        if ($model->status == Zakaz::STATUS_SUC_DISAIN) {
            $user_id = User::USER_DISAYNER;
        } else {
            $user_id = User::USER_MASTER;
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->status == Zakaz::STATUS_SUC_DISAIN) {
                    $model->unread('declined', 'declined', 'disain', 0);
                } else {
                    $model->unread('declined', 'declined', 'master', 0);
                }
                if (!$model->save()) {
                    $this->flashErrors($id);
                } else {
                    Yii::$app->session->addFlash('update', 'Работа была отклонена!');
                    $telegram->message($user_id, 'Отклонен заказ ' . $model->prefics . ' По причине: ' . $model->declined);
                }
                return $this->redirect(['admin', '#' => $model->id_zakaz]);
            } else {
                return $this->renderAjax('_declined', ['model' => $model]);
            }
        } else {
            return $this->renderAjax('_declined', ['model' => $model]);
        }
    }

    /**
     * * Zakaz accept admin and in appoint
     * if success then redirected view admin
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionAccept($id)
    {
        $model = $this->findModel($id);
        $telegram = new Telegram();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->status == Zakaz::STATUS_DISAIN or $model->status == Zakaz::STATUS_MASTER or $model->status == Zakaz::STATUS_AUTSORS) {
                    if ($model->status == Zakaz::STATUS_DISAIN) {
                        $model->unread(null, 'new', 'disain', 0);
                        $user_id = User::USER_DISAYNER;
                    } elseif ($model->status == Zakaz::STATUS_MASTER) {
                        $model->unread(null, 'new', 'master', 0);
                        $user_id = User::USER_MASTER;
                    } else {
                        $model->id_unread = 0;
                    }
                }
                if ($model->save()) {
                    /** @var $user_id \app\models\User */
                    $user = User::findOne(['id' => $user_id]);
                    if($model->status == Zakaz::STATUS_DISAIN && $user->telegram_chat_id){
                        $telegram->message($user_id, 'Назначен заказ '.$model->prefics.' '.$model->description);
                    }
                    Yii::$app->session->addFlash('update', 'Работа была принята');
                    return $this->redirect(['admin', 'id' => $id]);
                } else {
                    $this->flashErrors($id);
                }
            } else {
                return $this->renderAjax('accept', ['model' => $model]);
            }
        }
        return $this->renderAjax('accept', ['model' => $model]);
    }

    public function actionRefusing($id, $action)
    {
        $model = $this->findModel($id);
        if ($action == 'yes'){
            $model->action = 0;
            $model->save();
            return $this->redirect('admin');
        } else {
            $model->renouncement = null;
            $model->save();
            return $this->redirect(['update', 'id' => $id]);
        }
    }

    /** END Block admin in gridview*/
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

    /**
     * @param null $id
     */
    private function flashErrors($id = null)
    {
        /** @var $model \app\models\Zakaz */
        $id == null ? $model = new Zakaz() : $this->findModel($id);
        Yii::$app->session->addFlash('errors', 'Произошла ошибка! '.$model->getErrors());
    }
}

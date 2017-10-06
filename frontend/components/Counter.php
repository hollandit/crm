<?php
/**
 * Created by PhpStorm.
 * User: holland
 * Date: 05.07.2017
 * Time: 14:15
 */

namespace frontend\components;

use app\models\Courier;
use app\models\Custom;
use app\models\Helpdesk;
use app\models\Personnel;
use app\models\Todoist;
use app\models\Zakaz;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Nav;


class Counter extends Widget
{
    private $params;

    public function init()
    {
        parent::init();

        $zakaz = Zakaz::find();
        $todoist = Todoist::find();
        $custom = Custom::find();
        $helpdesk = Helpdesk::find();
        $shipping = Courier::find();
        $persnnel = Personnel::find();

        if (Yii::$app->user->can('shop')){
            $this->view->params['scoreZakazShop'] = $zakaz->andWhere(['id_sotrud' => Yii::$app->user->id, 'action' => 1])->count();
        } elseif (Yii::$app->user->can('disain')){
            $this->view->params['scoreDisain'] = $zakaz->andWhere(['status' => [Zakaz::STATUS_DISAIN, Zakaz::STATUS_SUC_DISAIN, Zakaz::STATUS_DECLINED_DISAIN]])->count();
        } elseif (Yii::$app->user->can('master')){
            $this->view->params['scoreMaster'] = $zakaz->andWhere(['status' => [Zakaz::STATUS_MASTER, Zakaz::STATUS_SUC_MASTER, Zakaz::STATUS_DECLINED_MASTER], 'action' => 1])->count();
        } elseif (Yii::$app->user->can('manager')){
            $this->view->params['scoreOrderZakaz'] = $zakaz->andWhere(['<', 'srok', date('Y-m-d H:i:s')])
                ->andWhere(['>', 'oplata', 1000])
                ->andWhere(['action' => 1])
                ->count();
        } elseif (Yii::$app->user->can('admin')){
            $this->view->params['scoreZakazAdmin'] = $zakaz->andWhere(['action' => 1])->count();
        }
        if (Yii::$app->user->can('admin')){
            $this->view->params['scoreTodoistAdmin'] = $todoist->andWhere(['id_user' => Yii::$app->user->id])
                ->andWhere(['<>', 'activate', Todoist::CLOSE])
                ->count();
        } elseif(Yii::$app->user->can('manager')){
            $this->view->params['scoreTodoistManager'] = $todoist->andwhere(['<', 'srok', date('Y-m-d')])
                ->andWhere(['activate' => !Todoist::CLOSE])
                ->count();
        } else {
            $this->view->params['scoreTodoist'] = $todoist->andWhere(['id_user' => Yii::$app->user->id])
                ->andWhere(['<>', 'activate', Todoist::CLOSE])
                ->count();
        }
        $this->view->params['scoreShippingAdmin'] = $shipping->andWhere(['<','status', Courier::RECEIVE])->count();
        $this->view->params['scoreShipping'] = $shipping->andWhere(['<','status', Courier::DELIVERED])->count();
        if (Yii::$app->user->can('system')){
            $this->view->params['scoreHelp'] = $helpdesk->andWhere(['status' => [Helpdesk::STATUS_NEW,Helpdesk::STATUS_CHECKING,Helpdesk::STATUS_DECLINED]])->count();
        } elseif (Yii::$app->user->can('manager')){
            $this->view->params['scoreHelpManager'] = $helpdesk->andWhere(['<', 'date', date('Y-m-d h:i:s')])
                ->andWhere(['status' => !Helpdesk::STATUS_APPROVED])->count();
        }else{
            $this->view->params['scoreHelp'] = $helpdesk->andWhere(['id_user' => Yii::$app->user->id, 'status' => [Helpdesk::STATUS_NEW,Helpdesk::STATUS_CHECKING,Helpdesk::STATUS_DECLINED]])->count();
        }
        if (Yii::$app->user)
        $this->view->params['scoreCustomZakup'] = $custom->andWhere(['action' => 0])->count();
        $this->view->params['scoreCustom'] = $custom->andWhere(['id_user' => Yii::$app->user->id, 'action' => 0])->count();
        $custom = Custom::find();
        $this->view->params['scoreCustomManager'] = $custom->andWhere(['<', 'date', date('Y-m-d H:i:s', strtotime('- 2 week'))])
                ->andWhere(['action' => Custom::CUSTOM_BROUGHT])->count();
//        $this->view->params['scoreShifts'] = $persnnel->andWhere()

    }

    /**
     * @return string
     */
    public function run()
    {
        return Nav::widget([
        'options' => ['class' => 'nav nav-pills headerNav'],
        'items' => [
            ['label' => 'Заказы <span class="badge pull-right">'.$this->view->params['scoreZakazAdmin'].'</span>', 'encode' => false, 'url' => ['zakaz/admin'], 'visible' => Yii::$app->user->can('seeAdmin')],
            ['label' => 'Заказы <span class="badge pull-right">'.$this->view->params['scoreZakazShop'].'</span>', 'encode' => false, 'url' => ['zakaz/shop'], 'visible' => Yii::$app->user->can('seeShop')],
            ['label' => 'Заказы <span class="badge pull-right">'.$this->view->params['scoreDisain'].'</span>', 'encode' => false, 'url' => ['zakaz/disain'], 'visible' => Yii::$app->user->can('disain')],
            ['label' => 'Заказы <span class="badge pull-right">'.$this->view->params['scoreMaster'].'</span>', 'encode' => false, 'url' => ['zakaz/master'], 'visible' => Yii::$app->user->can('master')],
            ['label' => 'Доставки <span class="badge pull-right">'.$this->view->params['scoreShipping'].'</span>', 'encode' => false, 'url' => ['courier/index'], 'visible' => Yii::$app->user->can('courier')],
            ['label' => 'Задачи <span class="badge pull-right">'.$this->view->params['scoreTodoistAdmin'].'</span>', 'url' => ['todoist/index'], 'encode' => false, 'visible' => Yii::$app->user->can('seeManager')],
            ['label' => 'Поломки <span class="badge pull-right">'.$this->view->params['scoreHelp'].'</span>', 'encode' => false, 'url' => ['helpdesk/index'], 'visible' => !(Yii::$app->user->can('manager') or Yii::$app->user->can('courier'))],
            ['label' => 'Закупки <span class="badge pull-right">'.$this->view->params['scoreCustom'].'</span>', 'encode' => false, 'url' => ['custom/adop'], 'visible' => Yii::$app->user->can('seeAdop')],
            ['label' => 'Доставки <span class="badge pull-right">'.$this->view->params['scoreShippingAdmin'].'</span>', 'encode' => false, 'url' => ['courier/shipping'], 'visible' => Yii::$app->user->can('admin')],
            ['label' => 'Закупки <span class="badge pull-right">'.$this->view->params['scoreCustomZakup'].'</span>', 'encode' => false,'url' => ['custom/index'], 'visible' => Yii::$app->user->can('zakup')],
            ['label' => 'Задачи <span class="badge pull-right">'.$this->view->params['scoreTodoist'].'</span>', 'encode' => false,'url' => ['todoist/shop'], 'visible' => !Yii::$app->user->can('seeManager')],
            ['label' => 'Заказы <span class="badge pull-right">'.$this->view->params['scoreOrderZakaz'].'</span>', 'encode' => false,'url' => ['zakaz/order'], 'visible' => Yii::$app->user->can('manager')],
            ['label' => 'Поломки <span class="badge pull-right">'.$this->view->params['scoreHelpManager'].'</span>', 'encode' => false,'url' => ['helpdesk/overdue'], 'visible' => Yii::$app->user->can('manager')],
            ['label' => 'Задачи <span class="badge pull-right">'.$this->view->params['scoreTodoistManager'].'</span>', 'encode' => false,'url' => ['todoist/overdue'], 'visible' => Yii::$app->user->can('manager')],
            ['label' => 'Заросы <span class="badge pull-right">'.$this->view->params['scoreCustomManager'].'</span>', 'encode' => false,'url' => ['custom/overdue'], 'visible' => Yii::$app->user->can('manager')],
            ['label' => 'Управляющий', 'encode' => false,'url' => ['site/manager'], 'visible' => Yii::$app->user->can('manager')],
        ],
    ]);
    }
}
<?php

namespace frontend\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use frontend\models\Telegram;

class TelegramController extends Controller
{
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = ($action->id !== "webhook");
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['webhook'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionWebhook()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        // file_put_contents('logs.txt', $content);
        if(isset($data['message']['chat']['id']))
        {
            $chatId = $data['message']['chat']['id'];
            file_put_contents('logs.txt', $chatId);
            $message = isset($data['message']['text']) ? $data['message']['text'] : false;

            $send = false;

            if (strpos($message, '/start') !== false) {
                $explode = explode(' ', $message);
                $token = isset($explode[1]) ? $explode[1] : false;
                $data = [
                    'raw' => $token,
                    'chat_id' => $chatId,
                ];
                $send = Telegram::start($data);
            } else {
                $send = 'Комманда не найдена. Если Вы уверены в том, что ошибка, обратитесь в тех поддержку';
            }
            $send = $send ? '' : 'Что-то пошло не по плану. Обратитесь в тех.поддержку';
        }
    }
}
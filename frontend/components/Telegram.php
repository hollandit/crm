<?php

namespace frontend\components;

use app\models\User;


$output = json_decode(file_get_contents('php://input'), true);
$message = $output['message'];
$id = $message['chat']['id'];
file_put_contents('logs.txt', $id);
$token = '414134665:AAHfOIdeikQD04NdKckL8wadhqzggvmSqw0';

$model = User::findOne(\Yii::$app->user->id);
$model->telegram_chat_id = $id;
$model->save();

$webSite = 'https://api.telegram.org/bot'.$token;


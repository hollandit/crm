<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "todoist".
 *
 * @property integer $id
 * @property string $date
 * @property string $srok
 * @property integer $id_zakaz
 * @property integer $id_user
 * @property integer $status
 * @property integer $typ
 * @property string $comment
 * @property integer $activate
 *
 * @property Zakaz $idZakaz
 * @property User $idUser
 */
class Todoist extends \yii\db\ActiveRecord
{
	const MOSCOW = 2;
	const PUSHKIN = 6;
	const SIBIR = 9;
	const ZAKUP = 10;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'todoist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['srok', 'comment'], 'required'],
            [['srok', 'date'], 'safe'],
            [['id_zakaz', 'id_user', 'status', 'typ', 'activate'], 'integer'],
            [['comment'], 'string'],
            [['id_zakaz'], 'exist', 'skipOnError' => true, 'targetClass' => Zakaz::className(), 'targetAttribute' => ['id_zakaz' => 'id_zakaz']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'srok' => 'Срок',
            'id_zakaz' => 'Заказ',
            'id_user' => 'Назначение',
            'status' => 'Статус',
            'typ' => 'Тип',
            'comment' => 'Доп.указание',
            'activate' => 'Activate',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdZakaz()
    {
        return $this->hasOne(Zakaz::className(), ['id_zakaz' => 'id_zakaz']);
    }

	/**
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id_user']);
    }

	public static function getIdUserArray()
	{
		return [
			self::MOSCOW => 'Московский',
			self::PUSHKIN => 'Пушкина',
			self::SIBIR => 'Сибирский',
			self::ZAKUP => 'Закупки',
		];
	}
	public function getIdUserName()
	{
		return ArrayHelper::getValue(self::getIdUser(), $this->id_user);
	}
}
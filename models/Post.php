<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "posts".
 *
 * @property int $id
 * @property string $author Имя автора
 * @property string $email Email
 * @property string $message Сообщение
 * @property string $ip_address IP
 * @property int $created_at
 * @property int|null $updated_at
 * @property string $manage_token Токен для редактирования
 */
class Post extends \yii\db\ActiveRecord
{
    const CAN_CREATE_MINUTES = 3 * 60;
    const CAN_EDIT_HOURS = 12 * 60 * 60;
    const CAN_DELETE_DAYS = 14 * 24 * 60 * 60;

    public $verifyCode;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['verifyCode', 'captcha', 'captchaAction' => 'post/captcha'],
            [['updated_at', 'created_at'], 'default', 'value' => time()],
            [['author', 'email', 'message', 'ip_address'], 'required'],
            [['message'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['email', 'manage_token'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 39],
            ['message', 'string', 'min' => 5, 'max' => 1000],
            ['author', 'string', 'min' => 2, 'max' => 15],
            ['email', 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author' => 'Имя автора',
            'email' => 'Email',
            'message' => 'Сообщение',
        ];
    }

    public function getUserPostsCount()
    {
        return static::find()->where(['ip_address' => $this->ip_address, 'is_deleted' => false])->count();
    }

    public function getMaskedIP()
    {
        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $arParts = explode('.', $this->ip_address);
            $arParts[2] = '**';
            $arParts[3] = '**';
            return implode('.', $arParts);
        }

        if (filter_var($this->ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $arParts = explode(':', $this->ip_address);
            $arParts[6] = '****';
            $arParts[7] = '****';
            return implode('.', $arParts);
        }
        return $this->ip_address; // если не IPv4, возвращаем как есть
    }

    public function softDelete()
    {
        $this->is_deleted = true;
        $this->deleted_at = time();

        return $this->save(false, ['is_deleted', 'deleted_at']);
    }

    public function canEdit()
    {
        return time() - $this->created_at <= static::CAN_EDIT_HOURS;
    }

    public function canDelete()
    {
        return time() - $this->created_at <= static::CAN_DELETE_DAYS;
    }

}

<?php

namespace app\models;

use app\listeners\PostCreatedListener;

/**
 * This is the model class for table "posts".
 *
 * @property int $id
 * @property bool $is_deleted
 * @property string $author Имя автора
 * @property string $email Email
 * @property string $message Сообщение
 * @property string $ip_address IP
 * @property int $created_at
 * @property int|null $updated_at
 * @property int|null $deleted_at
 * @property string $manage_token Токен для редактирования
 */
class Post extends \yii\db\ActiveRecord
{
    const CAN_CREATE_MINUTES = 3 * 60;
    const CAN_EDIT_HOURS = 12 * 60 * 60;
    const CAN_DELETE_DAYS = 14 * 24 * 60 * 60;

    public const EVENT_CREATED = 'postCreated';

    public static function tableName()
    {
        return 'posts';
    }

    public function init()
    {
        $this->on(self::EVENT_CREATED, [PostCreatedListener::class, 'handle']);

        parent::init();
    }

    public static function create(string $author, string $email, string $message, string $ip_address, string $manage_token): self
    {
        $newPost = new self();
        $newPost->created_at = time();
        $newPost->updated_at = time();

        $newPost->author = $author;
        $newPost->email = $email;
        $newPost->message = $message;

        $newPost->ip_address = $ip_address;
        $newPost->manage_token = $manage_token;

        return $newPost;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function setSoftDelete()
    {
        $this->is_deleted = true;
        $this->deleted_at = time();
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

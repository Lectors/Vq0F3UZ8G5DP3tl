<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\HtmlPurifier;

class PostForm extends Model
{
    public ?string $verifyCode = null;
    public ?int $id = null;
    public string $author = '';
    public string $email = '';
    public string $message = '';

    public function rules()
    {
        $arRules = [
            ['verifyCode', 'captcha', 'captchaAction' => 'post/captcha'],
            ['message', 'string'],
            ['message', 'string', 'min' => 5, 'max' => 1000],
            ['message', 'required'],
        ];
        if (!$this->id) {
            $arRules[] = ['author', 'string', 'min' => 2, 'max' => 15];
            $arRules[] = ['email', 'email'];
            $arRules[] = ['email', 'string', 'max' => 255];
            $arRules[] = [['author', 'email',], 'required'];
        }
        return $arRules;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author' => 'Имя автора',
            'email' => 'Email',
            'message' => 'Сообщение',
        ];
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $this->author = trim(HtmlPurifier::process($this->author, ['HTML.AllowedElements' => '']));
        $this->message = trim(HtmlPurifier::process($this->message, ['HTML.AllowedElements' => ['b', 'i', 's']]));

        return true;
    }

    public static function create(): self
    {
        $form = new self();
        $form->author = '';
        $form->message = '';
        return $form;
    }

    public static function loadFromEntity(Post $post): self
    {
        $form = new self();
        $form->id = $post->id;
        $form->message = $post->message;
        return $form;
    }
}
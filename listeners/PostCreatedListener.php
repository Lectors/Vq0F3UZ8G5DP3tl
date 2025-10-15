<?php

namespace app\listeners;

use yii\base\Event;
use Yii;
use yii\helpers\Url;

class PostCreatedListener
{
    public static function handle(Event $event): void
    {
        $post = $event->sender;

        $subject = 'Ваше сообщение успешно опубликовано';

        $body = '<h2>Ваше сообщение от ' . date('d.m.Y H:i:s', $post->created_at) . ' успешно опубликовано!</h2>';
        $body .= '<p><b>Текст сообщения:</b> ' . $post->message . '</p>';
        $body .= '<a href="' . Url::to(['post/edit', 'token' => $post->manage_token], true) . '">Редактировать сообщение</a><br>';
        $body .= '<a href="' . Url::to(['post/delete-confirm', 'token' => $post->manage_token], true) . '">Удалить сообщение</a>';

        Yii::$app->mailer->compose()
            ->setTo($post->email)
            ->setFrom([Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']])
            ->setSubject($subject)
            ->setHtmlBody($body)
            ->send();
    }
}
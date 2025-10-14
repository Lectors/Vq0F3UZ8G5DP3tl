<?php

use yii\helpers\Html;

/** @var \app\models\Post $model */
$this->title = 'Подтверждение удаления';
?>

<div class="post-confirm-delete">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Вы действительно хотите удалить это сообщение?</p>

    <div class="post-content">
        <blockquote>
            <?= $model->message ?>
        </blockquote>
        <p><small>Дата публикации: <?= Yii::$app->formatter->asDatetime($model->created_at) ?></small></p>
    </div>

    <div class="actions">
        <?= Html::beginForm(['post/delete', 'token' => $model->manage_token], 'post') ?>
        <?= Html::submitButton('Да, удалить', ['class' => 'btn btn-danger']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
        <?= Html::endForm() ?>
    </div>
</div>

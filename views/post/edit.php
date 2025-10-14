<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Post $model */

$this->title = 'Редактирование сообщения: ' . $model->id;
$this->params['breadcrumbs'][] = 'Редактирование сообщения ' . $model->id;
?>
<div class="post-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

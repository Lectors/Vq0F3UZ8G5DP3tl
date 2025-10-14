<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Post $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="post-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->errorSummary($model) ?>

    <? if ($model->id === null) { ?>
        <?= $form->field($model, 'author')->textInput(['maxlength' => true, 'placeholder' => 'Андрей']) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'yourmail.com']) ?>
    <? } ?>
    <?= $form->field($model, 'message')->textarea(['rows' => 6, 'placeholder' => 'Оставьте свой текстовый след в истории']) ?>

    <?= $form->field($model, 'verifyCode')->widget(\yii\captcha\Captcha::class, [
        'captchaAction' => 'post/captcha',
        'template' => '<div class="captcha-wrapper">{image} {input}</div>',
    ])->label('Введите код с картинки') ?>

    <div class="form-group">
        <?= Html::submitButton('Отправить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

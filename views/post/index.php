<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

$this->title = 'StoryValut';
?>
<div class="post-index">

    <div class="row">
        <div class="col-md-6">
            <div>
                <?= $data['countText'] ?>
            </div>
            <div class="pagination">
                <?= LinkPager::widget(['pagination' => $data['pagination']]) ?>
            </div>
            <? foreach ($data['items'] as $item) { ?>
                <div class="card card-default">
                    <div class="card-body">
                        <h5 class="card-title"><?= Html::encode($item['model']->author) ?></h5>
                        <p><?= $item['model']->message ?></p>
                        <p>
                            <small class="text-muted">
                                <?= Yii::t('app', '{createdAtRelative} | {ip} | {postsCount, plural, =0{нет постов} one{# пост} few{# поста} many{# постов} other{# поста}}', [
                                    'createdAtRelative' => Yii::$app->formatter->asRelativeTime($item['model']->created_at),
                                    'ip' => $item['ip_address'],
                                    'postsCount' => $item['msg_count'],
                                ]) ?>
                            </small>
                        </p>
                    </div>
                </div>
            <? } ?>
        </div>
        <div class="col-md-6">
            <?
            echo $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>

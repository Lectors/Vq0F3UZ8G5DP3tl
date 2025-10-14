<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'StoryValut';

$items = $dataProvider->getModels();
?>
<div class="post-index">

    <div class="row">
        <div class="col-md-6">
            <div>
                <?
                $count = $dataProvider->getCount();
                $pagination = $dataProvider->getPagination();
                $totalCount = $dataProvider->getTotalCount();
                $begin = $pagination->getPage() * $pagination->pageSize + 1;
                $end = $begin + $count - 1;
                if ($begin > $end) {
                    $begin = $end;
                }
                $page = $pagination->getPage() + 1;
                $pageCount = $pagination->pageCount;
                if ($count > 0) {
                    echo Yii::t('yii', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.', [
                        'begin' => $begin,
                        'end' => $end,
                        'count' => $count,
                        'totalCount' => $totalCount,
                        'page' => $page,
                        'pageCount' => $pageCount,
                    ]);
                } else {
                    echo 'Пока никто ничего не опубликовал';
                }
                ?>
            </div>
            <div class="pagination">
                <?= LinkPager::widget(['pagination' => $pagination]) ?>
            </div>
            <? foreach ($items as $item) { ?>
                <div class="card card-default">
                    <div class="card-body">
                        <h5 class="card-title"><?= Html::encode($item->author) ?></h5>
                        <p><?= $item->message ?></p>
                        <p>
                            <small class="text-muted">
                                <?= Yii::t('app', '{createdAtRelative} | {ip} | {postsCount, plural, =0{нет постов} one{# пост} few{# поста} many{# постов} other{# поста}}', [
                                    'createdAtRelative' => Yii::$app->formatter->asRelativeTime($item->created_at),
                                    'ip' => $item->getMaskedIP(),
                                    'postsCount' => $item->getUserPostsCount(),
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

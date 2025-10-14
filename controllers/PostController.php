<?php

namespace app\controllers;

use app\models\Post;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    public function actionIndex()
    {
        $newPost = new Post();
        if ($this->request->isPost) {

            $lastPost = Post::find()
                ->where(['ip_address' => Yii::$app->request->userIP, 'is_deleted' => false])
                ->orderBy(['id' => SORT_DESC])
                ->limit(1)
                ->one();

            if ($lastPost && (time() - $lastPost->created_at) < Post::CAN_CREATE_MINUTES) {
                $remaining = Post::CAN_CREATE_MINUTES - (time() - $lastPost->created_at);

                throw new ForbiddenHttpException('Вы можете отправлять сообщение не чаще чем раз в ' .
                    Yii::$app->formatter->asDuration(Post::CAN_CREATE_MINUTES) .
                    '. Новое сообщение можно отправить через ' .
                    Yii::$app->formatter->asDuration($remaining)
                );
            }

            if ($newPost->load($this->request->post())) {
                $newPost->ip_address = Yii::$app->request->userIP;
                $newPost->manage_token = Yii::$app->security->generateRandomString(64);

                $newPost->author = HtmlPurifier::process($newPost->author, ['HTML.AllowedElements' => '']);
                $newPost->message = HtmlPurifier::process($newPost->message, [
                    'HTML.AllowedElements' => ['b', 'i', 's'],
                ]);
                $newPost->message = trim($newPost->message);

                if ($newPost->save()) {
                    $this->sendManageEmail($newPost);
                    Yii::$app->session->setFlash('success', 'Сообщение успешно сохранено.');
                    return $this->redirect(['index']);
                }

            }
        } else {
            $newPost->loadDefaultValues();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Post::find()->where(['is_deleted' => false]),

            'pagination' => [
                'pageSize' => 3
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],

        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $newPost,
        ]);
    }

    public function actionEdit($token)
    {
        $post = Post::findOne(['manage_token' => $token, 'is_deleted' => false]);

        if ($post === null) {
            throw new ForbiddenHttpException('Неверный токен.');
        }
        if (!$post->canEdit()) {
            throw new ForbiddenHttpException('Редактирование доступно только в течение ' . Yii::$app->formatter->asDuration(Post::CAN_EDIT_HOURS) . ' после публикации.');
        }

        if ($this->request->isPost && $post->load($this->request->post())) {
            $post->message = HtmlPurifier::process($post->message, [
                'HTML.AllowedElements' => ['b', 'i', 's'],
            ]);
            $post->message = trim($post->message);

            if ($post->save(true, ['message'])) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно сохранено.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('edit', [
            'model' => $post,
        ]);
    }


    public function actionDelete($token)
    {
        $post = Post::findOne(['manage_token' => $token, 'is_deleted' => false]);

        if ($post === null) {
            throw new ForbiddenHttpException('Неверный токен.');
        }
        if (!$post->canDelete()) {
            throw new ForbiddenHttpException('Удаление доступно только в течение ' . Yii::$app->formatter->asDuration(Post::CAN_DELETE_DAYS) . ' после публикации.');
        }

        $post->softDelete();

        Yii::$app->session->setFlash('success', 'Сообщение удалено.');
        return $this->redirect(['index']);
    }

    public function actionDeleteConfirm($token)
    {
        $post = Post::findOne(['manage_token' => $token, 'is_deleted' => false]);

        if ($post === null) {
            throw new ForbiddenHttpException('Неверный токен.');
        }
        if (!$post->canDelete()) {
            throw new ForbiddenHttpException('Удаление доступно только в течение ' . Yii::$app->formatter->asDuration(Post::CAN_DELETE_DAYS) . ' после публикации.');
        }
        return $this->render('confirm-delete', [
            'model' => $post,
        ]);

    }

    private function sendManageEmail($post)
    {
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

<?php

namespace app\controllers;

use app\models\Post;
use app\models\PostForm;
use app\services\PostService;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class PostController extends Controller
{
    private PostService $service;

    public function __construct($id, $module, PostService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

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
        $newPostForm = PostForm::create();

        if ($this->request->isPost) {
            if ($newPostForm->load(Yii::$app->request->post()) && $newPostForm->validate()) {

                $lastPost = $this->service->getLastPostByIp(Yii::$app->request->userIP);

                if ($lastPost && (time() - $lastPost->created_at) < Post::CAN_CREATE_MINUTES) {
                    $remaining = Post::CAN_CREATE_MINUTES - (time() - $lastPost->created_at);

                    $newPostForm->addError('dummy', 'Вы можете отправлять сообщение не чаще чем раз в ' .
                        Yii::$app->formatter->asDuration(Post::CAN_CREATE_MINUTES) .
                        '. Новое сообщение можно отправить через ' .
                        Yii::$app->formatter->asDuration($remaining)
                    );
                } else {
                    $this->service->createPost($newPostForm, Yii::$app->request->userIP, Yii::$app->security->generateRandomString(64));
                    Yii::$app->session->setFlash('success', 'Сообщение успешно сохранено.');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('index', [
            'data' => $this->service->getPostsList(3),
            'model' => $newPostForm,
        ]);
    }

    public function actionEdit($token)
    {
        $post = $this->service->getPostByToken($token);

        if ($post === null) {
            throw new ForbiddenHttpException('Неверный токен.');
        }
        if (!$post->canEdit()) {
            throw new ForbiddenHttpException(
                'Редактирование доступно только в течение ' .
                Yii::$app->formatter->asDuration(Post::CAN_EDIT_HOURS) .
                ' после публикации.'
            );
        }

        $form = PostForm::loadFromEntity($post);

        if (Yii::$app->request->isPost && $form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->service->editPost($post, $form);
                Yii::$app->session->setFlash('success', 'Сообщение успешно сохранено.');
                return $this->redirect(['index']);
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('edit', [
            'model' => $form,
        ]);
    }


    public function actionDelete(string $token)
    {
        try {
            $this->service->deletePostByToken($token);
            Yii::$app->session->setFlash('success', 'Сообщение удалено.');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    public function actionDeleteConfirm(string $token)
    {
        try {
            $post = $this->service->ensureCanDelete($token);
        } catch (\DomainException $e) {
            throw new ForbiddenHttpException($e->getMessage());
        }

        return $this->render('confirm-delete', [
            'model' => $post,
        ]);
    }

}

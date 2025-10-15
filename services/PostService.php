<?php

namespace app\services;

use app\helpers\PostFormatter;
use app\models\Post;
use app\models\PostForm;
use app\repositories\PostRepository;
use app\repositories\PostRepositoryInterface;
use Yii;

class PostService
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createPost(PostForm $form, string $ip_address, string $manage_token): Post
    {
        $post = Post::create($form->author, $form->email, $form->message, $ip_address, $manage_token);
        $this->repository->save($post, true);

        return $post;
    }

    public function editPost(Post $post, PostForm $form): void
    {
        $post->setMessage($form->message);
        $this->repository->save($post);
    }

    public function getPostByToken(string $token): ?Post
    {
        return $this->repository->getByToken($token);
    }

    public function getLastPostByIp(string $ip): ?Post
    {
        return $this->repository->getLastPostByIp($ip);
    }

    public function deletePostByToken(string $token): void
    {
        try {
            $post = $this->ensureCanDelete($token);
        } catch (\DomainException $e) {
            throw new \DomainException($e->getMessage());
        }

        $this->repository->delete($post);
    }

    public function ensureCanDelete(string $token): Post
    {
        $post = $this->repository->getByToken($token);

        if ($post === null) {
            throw new \DomainException('Неверный токен.');
        }

        if (!$post->canDelete()) {
            throw new \DomainException(
                'Удаление доступно только в течение ' .
                \Yii::$app->formatter->asDuration(Post::CAN_DELETE_DAYS) .
                ' после публикации.'
            );
        }

        return $post;
    }

    public function getPostsList(int $pageSize = 3)
    {
        $dataProvider = $this->repository->getActiveDataProvider($pageSize);

        $posts = $dataProvider->getModels();
        $ips = array_unique(array_column($posts, 'ip_address'));
        $countsByIPs = $this->repository->getCountsByIp($ips);

        $items = [];
        foreach ($posts as $post) {
            $items[] = array(
                'model' => $post,
                'ip_address' => PostFormatter::maskIp($post->ip_address),
                'msg_count' => $countsByIPs[$post->ip_address],
            );
        }

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

        $countText = 'Пока никто ничего не опубликовал';
        if ($count > 0) {
            $countText = Yii::t(
                'yii',
                'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{item} other{items}}.',
                [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]
            );
        }

        return array(
            'items' => $items,
            'pagination' => $dataProvider->pagination,
            'countText' => $countText,
        );
    }
}
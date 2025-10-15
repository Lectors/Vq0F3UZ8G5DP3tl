<?php

namespace app\repositories;

use app\models\Post;
use yii\data\ActiveDataProvider;

class PostRepository implements PostRepositoryInterface
{
    public function getByToken(string $token): ?Post
    {
        return Post::find()
            ->where(['manage_token' => $token, 'is_deleted' => false])
            ->limit(1)
            ->one();
    }

    public function getLastPostByIp(string $ip): ?Post
    {
        return Post::find()
            ->where(['ip_address' => $ip, 'is_deleted' => false])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->one();
    }

    public function getActiveDataProvider(int $pageSize = 3): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => Post::find()->where(['is_deleted' => false]),

            'pagination' => [
                'pageSize' => $pageSize
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],

        ]);
    }

    public function getCountsByIp(array $ips): array
    {
        if (empty($ips)) {
            return [];
        }

        $rows = Post::find()
            ->select(['ip_address', 'cnt' => 'COUNT(*)'])
            ->where(['ip_address' => $ips, 'is_deleted' => false])
            ->groupBy('ip_address')
            ->asArray()
            ->all();

        return \yii\helpers\ArrayHelper::map($rows, 'ip_address', 'cnt');
    }

    public function save(Post $post, bool $bNew = false): void
    {
        if (!$post->save()) {
            throw new \RuntimeException('Ошибка сохранения поста: ' . json_encode($post->errors, JSON_UNESCAPED_UNICODE));
        }
        if ($bNew) {
            $post->trigger(Post::EVENT_CREATED);
        }
    }

    public function delete(Post $post): void
    {
        $post->setSoftDelete();
        if ($post->save() === false) {
            throw new \RuntimeException('Ошибка удаления поста.');
        }
    }
}
<?php

namespace app\repositories;

use app\models\Post;
use yii\data\ActiveDataProvider;

interface PostRepositoryInterface
{
    public function getByToken(string $token): ?Post;

    public function getLastPostByIp(string $ip): ?Post;

    public function getActiveDataProvider(int $pageSize = 3): ActiveDataProvider;

    public function getCountsByIp(array $ips): array;

    public function save(Post $post, bool $bNew = false): void;

    public function delete(Post $post): void;
}
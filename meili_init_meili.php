<?php
require 'vendor/autoload.php'; // 确保路径正确

use Meilisearch\Client;

// 连接 Meilisearch
$client = new Client('http://127.0.0.1:7700', ''); // 改成你的 URL 和 Key

// 创建索引 typecho_posts
try {
    $index = $client->createIndex('typecho_posts');
    echo "索引 typecho_posts 创建成功！";
} catch (\Meilisearch\Exceptions\ApiException $e) {
    echo "创建索引失败：" . $e->getMessage();
}

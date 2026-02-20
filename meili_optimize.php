<?php
require 'vendor/autoload.php';
use Meilisearch\Client;

$client = new Client('http://127.0.0.1:7700', '');
$index = $client->index('typecho_posts');

echo "Updating settings...\n";

/* 1️⃣ 关闭拼写容错 */
$index->updateTypoTolerance([
    'enabled' => false
]);

/* 2️⃣ 设置可搜索字段（标题优先） */
$index->updateSearchableAttributes([
    'title',
    'content'
]);

/* 3️⃣ 提高标题权重 */
$index->updateRankingRules([
    'words',
    'exactness',      // 精确匹配优先
    'proximity',
    'attribute',      // 按字段顺序权重
    'sort'
]);

echo "Optimization completed.\n";

<?php
require 'vendor/autoload.php';

use Meilisearch\Client;

// ===================
// Typecho 数据库配置
// ===================
$dbHost = 'localhost';
$dbName = 'typecho';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// ===================
// Meilisearch 配置
// ===================
$meiliHost = 'http://127.0.0.1:7700';
$meiliKey  = ''; // 如果你之前改为空，这里就留空
$client = new Client($meiliHost, $meiliKey);
$index = $client->index('typecho_posts');

// ===================
// 读取 Typecho 文章
// ===================
$stmt = $pdo->query("SELECT cid, title, slug, text, created, modified, status FROM typecho_contents WHERE type='post' AND status='publish'");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$articles) {
    die("没有文章可以同步。\n");
}

// ===================
// 准备同步数据
// ===================
$docs = [];
foreach ($articles as $a) {
    $docs[] = [
        'id'       => $a['cid'],
        'title'    => $a['title'],
        'slug'     => $a['slug'],
        'content'  => $a['text'],
        'created'  => $a['created'],
        'modified' => $a['modified'],
        'status'   => $a['status']
    ];
}

// ===================
// 推送到 Meilisearch
// ===================
try {
    $index->addDocuments($docs);
    echo "同步完成，共同步 " . count($docs) . " 篇文章到 Meilisearch。\n";
} catch (\Meilisearch\Exceptions\ApiException $e) {
    echo "同步失败：" . $e->getMessage() . "\n";
}

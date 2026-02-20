<?php
require 'vendor/autoload.php';
use Meilisearch\Client;

// =======================
// Meilisearch 配置
// =======================
$meiliHost = 'http://127.0.0.1:7700';
$meiliKey  = ''; // 如果你的 API Key 为空就留空
$indexName = 'typecho_posts';

$client = new Client($meiliHost, $meiliKey);
$index = $client->index($indexName);

// =======================
// 获取搜索关键词
// =======================
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

$hits = [];
$total = 0;

if ($keyword !== '') {
    try {
/*         $results = $index->search($keyword, [
			'matchingStrategy' => 'all',      // 所有词必须匹配
			'attributesToSearchOn' => ['title', 'content'], // 限定字段
			'limit' => 50
		]); 
*/
		
		$results = $index->search($keyword, [
			'attributesToHighlight' => ['title', 'content'],
			'attributesToCrop' => ['content'],
			'cropLength' => 40,   // 前后大约20个字
			'highlightPreTag' => '<span style="color:red">',
			'highlightPostTag' => '</span>',
			'showMatchesPosition' => true
		]);
		
        $hits = $results->getHits();                   // 获取搜索结果数组
        $total = $results->getEstimatedTotalHits();   // 获取总匹配数
    } catch (\Meilisearch\Exceptions\ApiException $e) {
        die("搜索失败: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Typecho 搜索</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        input[type=text] { width: 300px; padding: 5px; }
        input[type=submit] { padding: 5px 10px; }
        .result { margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; }
        .content { font-size: 14px; color: #555; }
    </style>
</head>
<body>
    <h1>搜索文章</h1>
    <form method="get" action="">
        <input type="text" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="输入关键词">
        <input type="submit" value="搜索">
    </form>

    <hr>

    <?php if ($keyword === ''): ?>
        <p>请输入关键词进行搜索。</p>
    <?php elseif (empty($hits)): ?>
        <p>未找到匹配文章。</p>
    <?php else: ?>
        <p>总匹配：<?php echo $total; ?> 篇文章</p>

		<?php foreach ($hits as $hit): ?>
			<div class="result">
				<div class="title">
					<a href="http://test.qy:8001/typecho/index.php/archives/<?php echo $hit['id']; ?>/" target="_blank">
						<?php echo $hit['_formatted']['title']; ?>
					</a>
				</div>

				<div class="content">
					<?php echo $hit['_formatted']['content']; ?>...
				</div>
			</div>
		<?php endforeach; ?>

    <?php endif; ?>
</body>
</html>

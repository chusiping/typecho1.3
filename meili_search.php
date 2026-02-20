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
        /* 全局基调：黑底白字，字体调小 */
        body {
            background-color: black;
            color: white;
            font-family: Arial, sans-serif;
            /* 相对于原样式的30px外边距不变，但整体字体基调缩小 */
            margin: 30px;
            /* 基础字体调小——原来依赖浏览器默认（通常是16px），现在设小一点 */
            font-size: 13px;          /* 整体调小，同时保持相对单位方便缩放 */
        }

        /* 输入框和按钮：为了在黑底上看得清，给输入框浅色背景+黑字，按钮也适配 */
        input[type=text] {
            width: 300px;
            padding: 5px;
            background-color: #333;     /* 深灰底，与黑底融合但可辨识 */
            color: white;               /* 白字 */
            border: 1px solid #666;
            font-size: 0.9rem;          /* 稍微控制大小，比全局稍小一点也协调 */
        }

        input[type=submit] {
            padding: 5px 10px;
            background-color: #222;
            color: white;
            border: 1px solid #777;
            font-size: 0.9rem;
            cursor: pointer;
        }
        /* 悬停效果增加一点交互反馈 */
        input[type=submit]:hover {
            background-color: #3a3a3a;
        }

        /* 结果区块：边框用浅灰避免刺眼，下边框也是浅灰 */
        .result {
            margin-bottom: 20px;
            border-bottom: 1px solid #444;   /* 原 #ddd 太亮，换成深色系灰 */
            padding-bottom: 10px;
        }

        /* 标题：白字，稍微加一点强调但仍旧小 */
        .title {
            font-size: 15px;          /* 原18px → 15px 调小 */
            font-weight: bold;
            color: white;
        }

        /* 内容文字：浅灰色（柔和白），尺寸更小一点 */
        .content {
            font-size: 12px;          /* 原14px → 12px 更小 */
            color: #ddd;               /* 柔白，不刺眼 */
        }

        /* 为了演示更丰富，加一小段辅助说明字体都调小了 */
        .note {
            font-size: 11px;
            color: #aaa;
            margin-top: 20px;
            border-top: 1px dotted #333;
            padding-top: 8px;
        }
    </style>
</head>
<body>
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

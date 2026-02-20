<?php
require 'vendor/autoload.php';
use Meilisearch\Client;

//实现多个匹配片段显示
function buildMultiFragments($content, $matches, $keyword, $radius = 30)
{
    if (empty($matches)) {
        return mb_substr(strip_tags($content), 0, 120) . '...';
    }
    $cleanContent = strip_tags($content);
    $fragments = [];
    $usedRanges = [];

    foreach ($matches as $match) {
        $start = max(0, $match['start'] - $radius);
        $end   = $match['start'] + $match['length'] + $radius;
        // 避免重复片段
        foreach ($usedRanges as $range) {
            if ($start <= $range[1] && $end >= $range[0]) {
                continue 2;
            }
        }
        $usedRanges[] = [$start, $end];
        $length = $end - $start;
        $fragment = mb_strcut($cleanContent, $start, $length);
        // 高亮关键词（支持多个）
        $words = explode(' ', trim($keyword, '"'));
        foreach ($words as $word) {
            if (!empty($word)) {
                $fragment = str_replace(
                    $word,
                    '<span class="search-highlight">' . $word . '</span>',
                    $fragment
                );
            }
        }
        $fragments[] = '...' . $fragment . '...';
    }
    return implode('<br>', $fragments);
}

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
if (mb_strlen($keyword) <= 4) {
    $keyword = '"' . $keyword . '"';
}


if ($keyword !== '') {
    try {
		$results = $index->search($keyword, [
            'matchingStrategy' => 'all',
			'attributesToHighlight' => ['title', 'content'],
			// 'attributesToCrop' => ['content'],
			'cropLength' => 40,   // 前后大约20个字
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
        .search-highlight {
            color: #d63638;
            font-weight: bold;
        }
        /* 全局基调：黑底白字，字体调小 */
        body {
            background-color: black;
            color: white;
            font-family: Arial, sans-serif;
            margin: 30px;
            font-size: 13px;
        }

        /* ---------- 链接强制白色（覆盖默认蓝色） ---------- */
        a {
            color: white;                  /* 默认链接白色 */
            text-decoration: underline;    /* 保留下划线清晰可辨，也可去除，这里选择保留但颜色白 */
            background-color: transparent; 
        }
        a:visited {
            color: #f0f0f0;                /* 访问后仍保持浅白，不出现紫色 */
        }
        a:hover {
            color: #ccc;                    /* 悬停变浅灰，给一点反馈 */
            text-decoration: underline;
        }
        a:active {
            color: #aaa;                    /* 激活时稍深一点 */
        }

        /* 输入框和按钮：深色底白字 */
        input[type=text] {
            width: 300px;
            padding: 5px;
            background-color: #333;
            color: white;
            border: 1px solid #666;
            font-size: 0.9rem;
        }

        input[type=submit] {
            padding: 5px 10px;
            background-color: #222;
            color: white;
            border: 1px solid #777;
            font-size: 0.9rem;
            cursor: pointer;
        }
        input[type=submit]:hover {
            background-color: #3a3a3a;
        }

        /* 结果区块 */
        .result {
            margin-bottom: 20px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }

        /* 标题 */
        .title {
            font-size: 13px;
            color: white;
        }

        /* 内容文字 */
        .content {
            font-size: 12px;
            color: #ddd;
        }

        /* 辅助说明 */
        .note {
            font-size: 11px;
            color: #aaa;
            margin-top: 20px;
            border-top: 1px dotted #333;
            padding-top: 8px;
        }

        /* 为了演示链接样式，额外添加一个链接容器 */
        .link-row {
            margin: 15px 0;
        }
        .link-row a {
            margin-right: 20px;
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
                    <?php
                        $content = $hit['content'];
                        $matches = [];
                        if (isset($hit['_matchesPosition']['content'])) {
                            $matches = $hit['_matchesPosition']['content'];
                        }
                        echo buildMultiFragments($content, $matches, $keyword, 100);
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<main id="main">
    <article class="post">
			<h2 class="post-title"><?php $this->title() ?></h2>
			<ul class="meta">
				<li><time><?php $this->date('Y-m-d'); ?></time></li>•
				<li><?php $this->category(','); ?></li>•
				<li><a><?php get_post_view($this) ?></a> 阅读</li>•
				<li><a href="<?php $this->permalink() ?>#comments"><?php $this->commentsNum('评论', '1 评论', '%d 评论'); ?></a></li>
			</ul>
        <div class="post-content" itemprop="articleBody">
            <?php $this->content(); ?>
        </div>
        <ul>
        <li>上一篇: <?php $this->thePrev('%s','没有了'); ?></li>
        <li>下一篇: <?php $this->theNext('%s','没有了'); ?></li>
        </ul>
        <p class="tags"><?php _e('标签: '); ?><?php $this->tags(', ', true, '无标签'); ?></p>
    </article>
    <?php $this->need('comments.php'); ?>
</main><!-- end #main-->


<?php $this->need('footer.php'); ?>

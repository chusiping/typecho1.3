<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once __DIR__ . '/vendor/autoload.php';

use Meilisearch\Client;

class MeiliSearch_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('MeiliSearch_Plugin', 'syncPost');
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->delete = array('MeiliSearch_Plugin', 'deletePost');
    }

    public static function deactivate(){}

    public static function config(Typecho_Widget_Helper_Form $form){}
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    public static function getClient()
    {
        return new Client('http://127.0.0.1:7700', '123456');
    }

    public static function syncPost($contents, $post)
    {
        $client = self::getClient();
        $index = $client->index('typecho_posts');

        $index->addDocuments([
            [
                'id' => $post->cid,
                'title' => $post->title,
                'content' => strip_tags($post->text),
                'created' => $post->created
            ]
        ]);
    }

    public static function deletePost($contents, $post)
    {
        $client = self::getClient();
        $index = $client->index('typecho_posts');
        $index->deleteDocument($post->cid);
    }
}

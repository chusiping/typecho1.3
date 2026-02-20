# typecho1.3

> 内容来自《md__typecho博客重建记录2026.md》
> 此git版本来自2026-2-19日大年初四，完成了typecho的从1.1到1.3的升级。

# 升级高版本

```
当前xampp版本：
	xampp-windows-x64-8.2.12-0-VS16-installer.exe
	XAMPP 8.2.12 带的是 PHP 8.2
	PHP 是 8.2：比 7.4 要求更严格，旧版 Typecho 跑不起来很正常

控制台新版本：
	XAMPP Control Panel v3.3.0 [ Compiled: Apr 6th 2021 ]
	XAMPP Control Panel v3.3.0
	
程序版本
	I:\侨银离职文件全部\离职2\E盘\typecho_离职版（git_15home.zip抽取的，版本为1.1/17.10.30） 
	（复制到 E:\xampp\htdocs\typecho）
	C:\Users\Administrator\Desktop\typecho1.3\ 
	（官方下载的最新版本，更新到htdocs\typecho）
	
数据版本：
	E:\git_15home\puppeteer_git\database\mysql数据库备份\typecho离职备份.psc 
	还原psc出错： 
		psc可直接在navicat里备份还原
		修改或添加以下配置
		E:\xampp\mysql\bin\my.ini (已存附件)
		[mysqld]
		max_allowed_packet = 256M
		wait_timeout = 600
		interactive_timeout = 600
		修改后可成功还原数据库typecho数据正常

升级到typecho1.3
	备份：config.inc.php 和 usr 文件夹
	删除文件夹 E:\xampp\htdocs\typecho\ 下的admin目录，var目录，index.php 文件
	将对应的新版本typecho1.3的admin,var，index.php 文件更新到对应为位置
	登录后台：访问 http://localhost/typecho/admin，系统会自动提示升级数据库，点击完成即可
```

# 重建依赖

```
E:\git_15home\typecho1.3\附件
    composer.phar
    coreseek41_typecho博客全文搜索.rar
    my_sphinx.bat
    typecho1.3.zip
    xampp-windows-x64-8.2.12-0-VS16-installer.exe
    E:\xampp\mysql\bin\my.ini （psc导入修改配置）
    composer.bat
```

# 全文索引 Meilisearch

> 全文搜索
> https://github.com/meilisearch/meilisearch/releases
> meilisearch-enterprise-windows-amd64.exe = "E:\meilisearch\meilisearch.exe"

## 1. composer安装

```shell
第一步：下载 composer.phar
		https://getcomposer.org/download/  选择Composer (composer.phar) versions history2.9.5	
第二步：composer.phar 放到E:\xampp\php\ 里
第三步：创建"E:\xampp\php\composer.bat"
		添加 ：@"E:\xampp\php\php.exe" "%~dp0composer.phar" %*  (环境变量问题换全路径)
		测试：
		C:\Windows\system32>E:\xampp\php\composer.bat  --version
		Composer version 2.9.5 2026-01-29 11:40:53
		PHP version 8.2.12 (E:\xampp\php\php.exe)
		Run the "diagnose" command to get more detailed diagnostics output.
```

## 2. Meilisearch安装

```
第一步：建 E:\meilisearch\，放入meilisearch-enterprise-windows-amd64.exe
第二步：运行 "E:\meilisearch\meilisearch.exe" （meilisearch-enterprise-windows-amd64.exe改为短名字）
第三步：http://127.0.0.1:7700  浏览
第四步：cmd里执行  meilisearch --master-key ""
第五步：composer require meilisearch/meilisearch-php
			E:\xampp\htdocs\typecho>E:\xampp\php\composer.bat require meilisearch/meilisearch-php
```

## 3. 以插件形式安装Meilisearch

​	第一步：创建插件目录 E:\xampp\htdocs\typecho\usr\plugins\meiliSearch  
​	第二步：创建文件 Plugin.php
​	第三步：后台 → 插件 → 启用
​	第四步：前端建 meili_search.php （E:\xampp\htdocs\typecho\meili_search.php）
​	第五步：访问http://test.qy:8001/typecho/meili_search.php?q=%E6%B5%8B%E8%AF%95
​	第六步：访问报错，安装 composer require guzzlehttp/guzzle php-http/guzzle7-adapter

```
报错：Fatal error: Uncaught Meilisearch ApiException: Http Status: 404 - Message: Index `typecho_posts` not found. - Code: index_not_found - Type: invalid_request - Link: https://docs.meilisearch.com/errors#index_not_found thrown in E:\xampp\htdocs\typecho\vendor\meilisearch\meilisearch-php\src\Http\Client.php on line 250
```

​	第七步：执行 http://test.qy:8001/typecho/meili_init_meili.php  测试：索引 typecho_posts 创建成功！
​	第八步：http://127.0.0.1:7700/indexes/typecho_posts/documents 如果报错，就把master-key 改为空，重启动服务
​	第九步：创建同步 meili_sync_meili.php, （http://test.qy:8001/typecho/meili_sync_meili.php）
​	测试结果：同步完成，共同步 550 篇文章到 Meilisearch。
​	再访问 Meilisearch 文档：http://127.0.0.1:7700/indexes/typecho_posts/documents

## 4. 快速测试

​	http://127.0.0.1:7700/indexes/typecho_posts/search?q=%E6%B5%8B%E8%AF%95

## 5.启动bat

​	start_typecho.bat （E:\xampp\htdocs\typecho\start_typecho.bat）启动apache，mysql，meilisearch三个服务，打开博客主页
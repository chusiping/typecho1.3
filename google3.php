<!-- 参考 ：https://blog.csdn.net/qiuyu6958334/article/details/88545238?utm_medium=distribute.pc_relevant.none-task-blog-2~default~baidujs_baidulandingword~default-0-88545238-blog-52134626.235^v43^control&spm=1001.2101.3001.4242.1&utm_relevant_index=3 -->

<?php 
// This for sphinx coreseek
// indexer -c D:/xampp____web/coreseek41/etc/csft_mysql.conf  --all 
// searchd --config D:/xampp____web/coreseek41/etc/csft_mysql.conf
require 'sphinxapi_coreseek41.php';

////////////////////////////////////////////////////////////////////////////

ini_set('max_execution_time','900');
define("WWWROOT",str_ireplace(str_replace("/","\\",$_SERVER['PHP_SELF']),'',__FILE__)."\\");
@$opt=$_GET['opt'];
//********** 设置bat参数 ********************
$root1 = WWWROOT."\\vhost\\bat\\";
//******************************************
if($opt!=null)
{
	global $opt;
	switch ($opt) {
		case 'my_sphinx': 
			exec($root1."my_sphinx.bat",$out);
			print_r($out);
			break; 
		default:
			exec($root1."test.bat",$out);
			print_r($root1."test.bat");
			print_r($out);
			break;    
		break;
	} 
	
}   

////////////////////////////////////////////////////////////////////////////


@$keyword=$_POST['keyword'];
$url_site = 'http://test.qy:8001/typecho/';
if($keyword!=null) {
	$sc = new SphinxClient();            
	$sc->setServer('127.0.0.1', 9312);    
	$indexname ='mysql';
	$sc->SetMatchMode(SPH_MATCH_ALL); //SPH_MATCH_PHRASE：必须匹配整个短语  SPH_MATCH_ALL：完全匹配所有的词
	if (strpos($keyword, '"') !== false) {
		$sc->SetMatchMode(SPH_MATCH_PHRASE);
	} 

	$res = $sc->query($keyword,$indexname);//     $sc->SetLimits(0,100);  //条数限制为200条
	$ids = (isset($res['matches']) && is_array($res)) ? $res['matches'] : [];
	$id = array_keys($ids);
	$id = implode(',',$id);
	// mysql_connect("127.0.0.1",'root','');
	// mysql_query('use typecho');
	// mysql_query('SET NAMES UTF8');

	$conn = new mysqli("127.0.0.1", 'root', '', 'typecho');
	if ($conn->connect_error) {
		die("连接失败: " . $conn->connect_error);
	}
	// $result = $conn->query($sql);

	$sql="select cid,title,text,FROM_UNIXTIME(created) date from typecho_contents where cid in($id) order by cid desc";
	// echo $sql;
	$res = $conn->query($sql) ;
	$list=array();

	//======== 判断是否是全词限定 =========
	$isAll = false;
	if(mb_strlen($keyword,'utf8')<4) {  $isAll = true; }
	if(strpos($keyword,"\"")>-1 ) {  $isAll = true; }
	//=================
	$opts = array(
        "before_match"=> '<span style="color:blue;font-weight:bold;">',
        "after_match" => '</span>',
        "chunk_separator"=> '<br><br>',
        "around" => 16,
        "exact_phrase" => $isAll
    );
	// var_dump($isAll); 
	$list = $res->fetch_all(MYSQLI_ASSOC);
	// while($row=mysql_fetch_assoc($res)){
	//     $list[]=$row;
	// }
}
?>
<!DOCTYPE HTML>
<html class="no-js">
<head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>在水一方--google</title>
        <meta name="robots" content="noindex, nofollow">
        <link rel="stylesheet" href="<?php echo $url_site; ?>admin/css/normalize.css?v=17.10.30">
		<link rel="stylesheet" href="<?php echo $url_site; ?>admin/css/grid.css?v=17.10.30">
		<link rel="stylesheet" href="<?php echo $url_site; ?>admin/css/style.css?v=17.10.30">
		<script src="https://cdn.staticfile.org/jquery/3.4.0/jquery.min.js"></script>
		<script src="https://cdn.staticfile.org/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
		<style>
            .container {
                max-width: 100%;
            }
			#main {
                width: 90%;
            }
			.colored-background {
				background-color: #e8e5e5;
			}
        </style>
</head>
<body class="body">
<div class="container clearfix">
    <div>
		<select id="opt" class="btn">
            <option value="">BAT操作</option>
			<option value="my_sphinx" selected>sphinx重建</option>
            <option value="2">测试</option>
        </select>
        <button id="start" type="button" class="primary btn"> 开始 </button>
        <div style="padding: 10px; display: inline-block;">
            <form action="" method="post" name="login" role="form">
                <datalist id="cp1_datalist"></datalist>
                <input type="text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="text-l" autofocus="" list="cp1_datalist">
                <button id="bt_1" type="submit" onclick="save_input();" class="btn btn-l primary">搜索</button>
            </form>
        </div>
    </div>   
    <div id="main" style="display: flex; flex-wrap: wrap; gap: 10px;"> 
        <?php
            if (isset($list)){
                foreach($list as $v){
                    $row = $sc->buildExcerpts($v,$indexname,$keyword,$opts);
        ?>
            <article class="post colored-background" style="flex: 1 1 calc(25% - 10px); box-sizing: border-box; padding: 10px; border: 1px solid #ddd;">
                <div style="font-size: 15px; font-weight: bolder;">
                    <a href="<?php echo $url_site; ?>index.php/archives/<?php echo $row[0]; ?>" target="_blank"><?php echo $row[1]; ?></a>
                </div>
                <div> 
                    <a href="<?php echo $url_site; ?>/index.php/archives/<?php echo $row[0]; ?>/?key=<?php echo $keyword; ?>" target="_blank"><?php echo $row[3]; ?></a> 
                </div>
                <div class="post_content">
                    <?php echo $row[2]; ?>
                </div>
            </article>
        <?php } }  ?>
    </div>
</div>


<script language="JavaScript">  
    $(document).ready(function(){
        BindDataList();	
		$('#start').click(function(){ 
            window.location.href="?opt="+ $("#opt").val();
        }) 
    });
	function BindDataList()		//把cookie里的数据绑定到所有的datalist
	{
		var c_start=document.cookie.indexOf("history=");
		if(c_start == -1) return;
		var HistoryCookie = $.cookie('history');
		var hisObject = JSON.parse(HistoryCookie); //字符串转化成JSON数据		
		for(var i=0;i<hisObject.length;i++){
			for(var key in hisObject[i]){
				//alert(key+':'+hisObject[i][key]);
				$('#'+ key +'').append('<option value="' + hisObject[i][key] + '"></option>'); ////给指定的datalist添加option
			}
		}
	}
	function SetBataListCookie(inputID,datalistID)  //把input里的输入历史记录到cookie
	{
		var c_start=document.cookie.indexOf("history=");
		var HistoryCookie = null; 
		var hisObject = [];
		var vl = $(inputID).val();
		if(vl == "") return;
		var newob = {}; newob[datalistID] = vl ;
		if(c_start > -1) {
			HistoryCookie = $.cookie('history');
			hisObject = JSON.parse(HistoryCookie); //字符串转化成JSON数据
			for(var i=0;i<hisObject.length;i++){
				for(var key in hisObject[i]){
					if(key == datalistID && hisObject[i][key] == vl ) return;
				}
			}
		}		
		hisObject.push(newob);
		var objString = JSON.stringify(hisObject); 
		$.cookie('history',objString);
		$('#'+ datalistID +'').html('');
		BindDataList();		
	}
	function save_input()
	{
		SetBataListCookie('#cp1','cp1_datalist');
	}
</script>
<script src="<?php echo $url_site; ?>admin/js/jquery.js?v=17.10.30"></script>
<script src="<?php echo $url_site; ?>admin/js/jquery-ui.js?v=17.10.30"></script>
<script src="<?php echo $url_site; ?>admin/js/typecho.js?v=17.10.30"></script>
</body>
</html>



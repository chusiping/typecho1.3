<?php 
// 位置 Z:\xampp_portable\xampp5633\htdocs\vhost\blog_typecho
// http://win7.qy:8001/api_typecho.php
// This for sphinx coreseek
// indexer -c D:/xampp____web/coreseek41/etc/csft_mysql.conf  --all 
// searchd --config D:/xampp____web/coreseek41/etc/csft_mysql.conf
require 'sphinxapi_coreseek41.php';

@$keyword=$_GET['kword'];
if($keyword!=null) {
	$sc = new SphinxClient();            
	$sc->setServer('127.0.0.1', 9312);    
	$indexname ='mysql';
	$sc->SetMatchMode(SPH_MATCH_PHRASE); //SPH_MATCH_PHRASE：必须匹配整个短语  SPH_MATCH_ALL：完全匹配所有的词

//     $sc->SetLimits(0,100);  //条数限制为200条
	$res = $sc->query($keyword,$indexname);
	$ids = $res['matches'];
	$id = array_keys($ids);
	$id = implode(',',$id);
	mysql_connect("127.0.0.1",'root','1qaz@WSX');
	mysql_query('use typecho');
	mysql_query('SET NAMES UTF8');
	$sql="select cid,title,text,FROM_UNIXTIME(created) date from typecho_contents where cid in($id) order by cid desc";
	// echo $sql;
	$res = mysql_query($sql) ;
	// $list=array();
    // $rt = json_encode($list); 
    $list=array();

    //======== 判断是否是全词限定 =========
    $isAll = false;
    if(mb_strlen($keyword,'utf8')<4) {  $isAll = true; }
    if(strpos($keyword,"\"")>-1 ) {  $isAll = true; }
    //=================
    $opts = array(
        "before_match"=> '<span style="color:blue;font-weight:bold;">',
        "after_match" => '</span>',
        "chunk_separator"=> '...',
        "around" => 16,
        "exact_phrase" => $isAll
    );

    while($row=mysql_fetch_assoc($res)){
	    $list[]=$row;
	}
    if (isset($list)){
        $rt2=array();
        foreach($list as $v){
            $row = $sc->buildExcerpts($v,$indexname,$keyword,$opts);
            // print_r($row);
            $txt = $row[2];
            
            $row2 = array(
                "cid"=> $row[0],
                "title" =>$row[1],
                // "text"=> $row[2],
                "text"=> $txt,
                "date" => $row[3]
            );
            // $row = json_encode($row); 
            // echo($row); //cid,title,text,date
            array_push($rt2,$row2);
        }
        // $str =  json_encode($rt2);// /<iframe(([\s\S])*?)<\/iframe>/
        // $str = preg_replace('/<font(([\s\S])*?)<\/font>/', '', $str);
        // echo($rt2);
        echo(json_encode($rt2));
    }
}

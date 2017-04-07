<?php
	
	$perRow = 10;                         //每次从redis中取出10条
	header('Content-Type: text/html; charset=utf8');
	//连接数据库
	$link = mysql_connect("localhost","root","root");
	if(!$link){
		echo '数据库连接失败...<br>';
		exit(-1);
	}else{
		echo "数据库连接成功...<br>";
	}
	mysql_set_charset('utf8');
	mysql_select_db('weiqin');
	
	$redis = new redis();  
	$redis->connect('127.0.0.1', 6379);  
    
	
	$res = $redis->sort('award_id');
	
	if(empty($res)){
		mysql_close($link);
		die;
	}
	$length = min(count($res),$perRow); 
	$str = '';
	for($i=0;$i<$length;$i++){
		$str .=$res[$i].',';
	}
	$str = trim($str,',');
	$sql = "UPDATE wq_mon_jgg_user_award SET status=1 WHERE id in(".$str.")";
	
	$res_up = mysql_query($sql);
	
	if($res_up){
		for($i=0;$i<10;$i++){
			$redis->sremove('jgg_award_id',$res[$i]);
		}
	}
	//关闭数据库连接
	mysql_close($link);
?>
<?php/** *把获奖信息下载到excel表中 * */require 'Excel.class.php';
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');}
global $_GPC, $_W;$keywords= $_GPC['keywords'];$uid = intval($_GPC['uid']);
$jid = intval($_GPC['jid']);
if (empty($jid)) {
    message('抱歉，传递的参数错误！', '', 'error');
}$params[':jid'] = $jid;$status=-1;if(!empty($_GPC['status'])){    $status=$_GPC['status'];	}$where = '';if($status==2){	$where .= ' and a.status=0 and p.prize_type=0';}if($status==1){	$where .= ' and (( a.status=1 and p.prize_type=0) or p.prize_type!=0)';}if(!empty($uid)){	$where .= ' and u.openid = a.openid and u.id=:id';	$params[':id'] = $uid; }if (!empty($keywords)) {    $where .= ' and u.nickname like :nickname';    $params[':nickname'] = "%$keywords%";}
$sql = "SELECT a.openid,a.award_name,a.prize_value,a.status,a.createtime,u.nickname,p.prize_level,p.prize_type FROM ".tablename(CRUD::$table_sudo_ku_user_award)." a 			left join ".tablename(CRUD::$table_sudo_ku_user)." u on a.openid=u.openid  			left join ".tablename(CRUD::$table_sudo_ku_prize)." p on p.prize_id=a.prize_id 			WHERE a.jid =:jid ";
$list = pdo_fetchall($sql.$where." ORDER BY  a.createtime DESC", $params);$excel = new Excel();$excel->addHeader(array('openid','奖品名称','奖品虚拟等值','状态','中奖时间','昵称','奖品等级'));		foreach($list as $key =>$val){		if($val['status']==0){			if($val['prize_type']==0){				$list[$key]['status'] = '未处理';			}else{				$list[$key]['status'] =='已发奖';			}		}else{			$list[$key]['status'] ='已发奖';		}		$list[$key]['createtime'] = date('Y-m-d H:i:s',$val['createtime']);		unset($list[$key]['prize_type']);	}		$excel->addBody($list);	$excel->downLoad();?>

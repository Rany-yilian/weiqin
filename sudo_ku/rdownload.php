<?php/** *把抽奖记录下载到excel表中 * */require 'Excel.class.php';
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');}
global $_GPC, $_W;
$jid = intval($_GPC['jid']);
if (empty($jid)) {
    message('抱歉，传递的参数错误！', '', 'error');
}
$where = '';
$params = array(
       ':jid' => $jid
);;
if(!empty($_GPC['uid'])){
     $where .= " and u.id=:id";
     $params[':id'] = $_GPC['uid'];
}$keyword = $_GPC['keywords'];if (!empty($keyword)) {
        $where .= ' and u.nickname like :nickname';
        $params[':nickname'] = "%$keyword%";
}$sql = "SELECT r.*,u.nickname,p.prize_level FROM " .				 tablename(CRUD::$table_sudo_ku_user_record) . " r 				left join ".tablename(CRUD::$table_sudo_ku_user)." u on r.openid=u.openid 				left join ".tablename(CRUD::$table_sudo_ku_prize)." p on p.prize_id=r.prize_id 				WHERE r.jid =:jid";
$list = pdo_fetchall($sql.$where." ORDER BY r.createtime",$params);$excel = new Excel();$excel->addHeader(array('openid','奖品名称','抽奖时间','昵称','奖品等奖'));		foreach($list as $key =>$val){		foreach($val as $k => $v){			if($k == 'openid'){				$data[$key][$k] = $v;				continue;			}			if($k == 'award_name'){				if(empty($v)){					$data[$key][$k]='没中奖';				}else{					$data[$key][$k] = $v;				}				continue;			}			if($k == 'createtime'){				$data[$key][$k] = date('Y-m-d H:i:s',$v);				continue; 			}			if($k == 'nickname'){				$data[$key][$k] = $v;								continue;			}			if($k == 'prize_level'){				if(empty($v)){					$data[$key][$k]='没中奖';				}else{					$data[$key][$k] = $v;				}				continue;			}		}	}		$excel->addBody($data);	$excel->downLoad();?>

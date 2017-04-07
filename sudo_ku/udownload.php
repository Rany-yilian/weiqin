<?php/** *把用户信息下载到excel表中 * */require 'Excel.class.php';
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');}
	global $_GPC, $_W;
	$jid = intval($_GPC['jid']);
	if (empty($jid)) {
		message('抱歉，传递的参数错误！', '', 'error');
	}	$params[':jid'] = $jid;		if(!empty($_GPC['keywords'])){		$where .=' and nickname like :nickname';		$params[':nickname'] = '%'.$_GPC['keywords'].'%';	}
	$list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sudo_ku_user) ." WHERE jid =:jid ".$where." ORDER BY createtime DESC", $params);
	$excel = new Excel();	$excel->addHeader(array('openid','昵称','金币数'));		foreach($list as $key =>$val){		foreach($val as $k => $v){			if($k == 'openid'){				$data[$key][$k] = $v;				continue;			}			if($k == 'nickname'){				$data[$key][$k] = $v;				continue;			}			if($k == 'coin'){				$data[$key][$k] = $v; 			}		}	}		$excel->addBody($data);	$excel->downLoad();
	?>

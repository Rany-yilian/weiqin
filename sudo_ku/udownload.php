<?php
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');
	global $_GPC, $_W;
	$jid = intval($_GPC['jid']);
	if (empty($jid)) {
		message('抱歉，传递的参数错误！', '', 'error');
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sudo_ku_user) ." WHERE jid =:jid ".$where." ORDER BY createtime DESC", $params);

	
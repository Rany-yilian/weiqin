<?php
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');
global $_GPC, $_W;
$jid = intval($_GPC['jid']);
if (empty($jid)) {
    message('抱歉，传递的参数错误！', '', 'error');
}
$where = '';
$params = array(
       ':jid' => $jid
);
if(!empty($_GPC['uid'])){
     $where .= " and u.id=:id";
     $params[':id'] = $_GPC['uid'];
}
        $where .= ' and u.nickname like :nickname';
        $params[':nickname'] = "%$keyword%";
}
$list = pdo_fetchall($sql.$where." ORDER BY r.createtime",$params);
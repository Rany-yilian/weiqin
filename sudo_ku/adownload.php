<?php
if (PHP_SAPI == 'cli'){
    die('This example should only be run from a Web Browser');
global $_GPC, $_W;
$jid = intval($_GPC['jid']);
if (empty($jid)) {
    message('抱歉，传递的参数错误！', '', 'error');
}
$sql = "SELECT a.openid,a.award_name,a.prize_value,a.status,a.createtime,u.nickname,p.prize_level,p.prize_type FROM ".tablename(CRUD::$table_sudo_ku_user_award)." a 
$list = pdo_fetchall($sql.$where." ORDER BY  a.createtime DESC", $params);
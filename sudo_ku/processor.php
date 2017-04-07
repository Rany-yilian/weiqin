<?php



defined('IN_IA') or exit('Access Denied');
define("SUDO_KU", "sudo_ku");

require_once IA_ROOT . "/addons/" . SUDO_KU . "/CRUD.class.php";
class Sudo_kuModuleProcessor extends WeModuleProcessor {

    private $sae=false;

    public function respond() {		global $_W;
        $rid = $this->rule;
		
        $jgg=pdo_fetch("select * from ".tablename(CRUD::$table_sudo_ku)." where rid=:rid",array(":rid"=>$rid));
		
        if(!empty($jgg)){
            if(time()<$jgg['starttime']){
                return   $this->respText("活动未开始");
            }elseif(time()>$jgg['endtime']){
               return  $this->respText("活动已结束");
            }else{
                $from=$this->message['from'];
                $news = array ();
                $news [] = array (
					'title' => $jgg['title'], 
					'description' =>$jgg['description'],
					'picurl' => $_W['attachurl'].$jgg['start_picurl'], 
					'url' => $this->createMobileUrl ( 'entrance',array('openid'=>$from,'jid'=>$jgg['id']))  
				);
                return $this->respNews ( $news );
            }
        }else{
          return   $this->respText("此活动已删除或不存在");
        }
        return null;
    }
}

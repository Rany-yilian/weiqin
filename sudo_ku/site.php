<?php

defined('IN_IA') or exit('Access Denied');

define("SUDO_KU", "sudo_ku");
define("SUDO_KU_RES", "../addons/" . SUDO_KU . "/");
require_once IA_ROOT . "/addons/" . SUDO_KU . "/CRUD.class.php";
require IA_ROOT . "/addons/" . SUDO_KU . "/oauth2.class.php";
require IA_ROOT ."/addons/".SUDO_KU."/function.php";

/**
 * Class Sudo_KuModuleSite
 *
 */
class Sudo_KuModuleSite extends WeModuleSite
{
    public $weid;
    public $acid;
    public $oauth;
    public static $STATUS_UNDO=0;//未处理
   
    function __construct(){
        global $_W,$_GPC;
        $this->weid = $_W['uniacid'];
        $this->oauth = new Oauth2($_W['account']['key'],$_W['account']['secret']);
    }
	
	/**
	 *授权登录
	 *
	 */
	public function doMobileEntrance(){
		global $_W;
		$setting = $this->findSettings($_W['uniacid'],$_W['current_module']['name']);
		
		$url = $_W['siteurl'];
		$res = preg_replace('/entrance/','index',$url);
		$head_url = get_url_snsapi_base($setting['appid'],$res);
		header("Location:$head_url");
	}
	
	/**
	 *抽奖主页面
	 *
	 */
	public function  doMobileIndex(){
        global $_W,$_GPC;
		$this->checkmobile();
		
		$setting = $this->findSettings($_W['uniacid'],$_W['current_module']['name']);
		
		$code = $_GPC['code'];           //得到另一个公众号上的openid
		$res_openid = get_openid_by_code($code,$setting['appid'],$setting['secret']);
		$res_openid = json_decode($res_openid,true);
		
		$jid = $_GPC['jid'];
		$jgg=CRUD::findById(CRUD::$table_sudo_ku,$jid);
		
		if(empty($jgg)){
			$tips = '活动已删除或不存在';
            include $this->template('tips');die;
        }
		if($jgg['starttime']>TIMESTAMP || $jgg['endtime']<TIMESTAMP){
			$tips = '活动已结束';
			include $this->template('tips');die;
		}
		$count = CRUD::findCount(CRUD::$table_sudo_ku_share,array(':openid'=>$_W['openid'],':jid'=>$jid));
		
		/**
		 *若为1，表示需关注后再进行抽奖
		 *
		 */
		$res_user['subscribe'] = 1;
		if($jgg['isSubscribe']==1){                  
			$res_user = $this->getInfo($_W['openid']);
		}
		
		/**
		 *若不为空，表示有城市限制
		 *
		 */
		if(!empty($jgg['perssion_city'])){
			$arr = explode(',',$jgg['perssion_city']);
			$ip = $_W['clientip'];
			$address = get_address($ip);
			$this->isPermitAddress($arr,$address);
		}
		$prize_res=CRUD::findPrizeByJid(CRUD::$table_sudo_ku_prize,$jid);
		
        $openid = $_W['openid'];	
        if (!empty($openid)) {
           $userInfo=$this->getUserInfo($openid);
        }
		
		if(!empty($userInfo['nickname'])){
		
			$result = CRUD::findList(CRUD::$table_sudo_ku_user,array(':jid'=>$jid,':openid' =>$openid));
			if(empty($result)){
				$data = array();
				$data = array(
					'jid'             => $jid,
					'openid'       => $openid,
					'nickname'   => $userInfo['nickname'],
					'other_openid' => $res_openid['openid'],
					'headimgurl' => $userInfo['headimgurl'],
					'last_time'    => TIMESTAMP,
					'leftPlayTimes' =>$jgg['peruserday_num_times'],
					'createtime'  => TIMESTAMP
				);
				CRUD::create(CRUD::$table_sudo_ku_user,$data);
				
			}else{
				$data = array();
				$data['last_time'] = TIMESTAMP;
				CRUD::update(CRUD::$table_sudo_ku_user,$data,array('jid'=>$jid,'openid' =>$openid));
			}
		}
		$this->updatePlayUser($jid,$openid,$jgg['peruserday_num_times']);
		
		$res_share_time = CRUD::findDayCount(CRUD::$table_sudo_ku_share,array(':openid'=>$openid,':jid'=>$jid,':status'=>0));   //分享朋友圈次数
		$res_share_app = CRUD::findDayCount(CRUD::$table_sudo_ku_share,array(':openid'=>$openid,':jid'=>$jid,':status'=>1));   //分享朋友次数
		
        $res = $this->findPlayUser($jid,$openid);
		$leftPlayCount = empty($res[0]['leftPlayTimes'])? 0 : $res[0]['leftPlayTimes'];
		
        include $this->template('index');
    }
	
	/**
	 *规则页面
	 *
	 */
	public function doMobileDesc(){
		global $_GPC,$_W;
		$jid = $_GPC['jid'];
		$jgg=CRUD::findById(CRUD::$table_sudo_ku,$jid);
		
		$openid = $_W['openid'];	
        if (!empty($openid)) {
           $userInfo=$this->getUserInfo($openid);
        }
		
		include $this->template('desc');
	}
	
	 /**
     * 开始抽奖
	 *
     */
    public function  doMobilePlay(){
        global $_GPC,$_W;
		
        $jid=$_GPC['jid'];
        $openid=$_GPC['openid'];
        $jgg = CRUD::findById(CRUD::$table_sudo_ku, $jid);     
        if(empty($jgg)){
			sendJson(500,"九宫格活动删除或不存在!");
        }
        if(TIMESTAMP<$jgg['starttime']){
			sendJson(500,"活动还未开始呢，歇会再来吧!");
        }
        if(TIMESTAMP>$jgg['endtime']){
			sendJson(500,"活动已结束，下次再来吧!");
        }
        $clientUser = $this->getClientUserInfo();
        if (empty($clientUser)) {
			sendJson(500,"请授权登录后再进行抽奖哦!");
        }
		
        $res = $this->findPlayUser($jid,$openid);
		$leftPlayCount = $res[0]['leftPlayTimes'];
        if($leftPlayCount<=0){
			sendJson(500,"抽奖机会已用完");
        }
		
        $res=$this->createPlayRecored($jgg,$clientUser['openid']);
		$data=array();
        $data['code'] = 200;
        $data['prize'] = $res['prize'];
		$data['user_award_id'] = $res['user_award_id'];
		$data['prize_type'] = $res['prize_type'];
        $data['status']=1;
        echo json_encode($data);
        die;		
    }
	
	/**
	 *拆红包
	 *
	 */
	public function doMobileSeparate(){
		global $_W,$_GPC;
		$this->checkmobile();
		
		$user_award_id=$_GPC['user_award_id'];
		
		$res = $this->get_pri_award_res($_W['openid'],$user_award_id);
		
		$res['prize_value'] = sprintf('%.2f',$res['prize_value']);
		$account_api = WeAccount::create();
		$token = $account_api->getAccessToken();
		if(is_error($token)){
			message('服务繁忙，请重新进入');die;
		}
		$userInfo=json_decode(get_fan_info($token,$res['openid']),true);
		include $this->template('chai');
	}
	
	/**
	 *领取红包
	 *
	 */
	 public function doMobileReceiveRed(){
		global $_W,$_GPC;
		
		$this->checkmobile();
		$user_award_id=$_GPC['user_award_id'];
		$res = CRUD::findListEx(CRUD::$table_sudo_ku_user_award,'jid,openid,award_name,prize_id,prize_value',array(':id'=>$user_award_id,':status'=>0));		
		
		$user = $this->getUsers($res[0]['jid'],$res[0]['openid']);    //得到用户信息
		
		$result = $this->send_qyfk($res[0]['prize_value'],$user['other_openid'],$_W['account']['name']);
		
		$data = array();
		if($result['code']==0){
			$res_up_award=pdo_update('sudo_ku_user_award',array('status'=>1),array('jid'=>$res[0]['jid'],'openid'=>$res[0]['openid'],'prize_id' => $res[0]['prize_id']));
			if(empty($res_up_award)){
				$redis = new Redis(); 
				$redis->connect('127.0.0.1', 6379);
				$redis->sadd('award_id',$user_award_id);  		
			}
			sendJson(0,'领取红包成功');
		}else{
			sendJson(1,'领取红包失败');
		}
	 }

	/**
	 * 我的奖品
	 *
	 */
	 public function doMobilePrize(){
		global $_W,$_GPC;
		$this->checkmobile();
		$jid = $_GPC['jid'];
		$award = CRUD::findList(CRUD::$table_sudo_ku_user_award,array(':openid'=>$_W['openid'],':jid'=>$jid));	
		include $this->template('prize');
	 }
	 
	 /**
	  *下拉刷新获得奖品
	  *
	  */
	 public function doMobilePrizeRefresh(){
		global $_W,$_GPC;
		$page = (int)$_GPC['page'];
		$length = 8;
		$jid = $_GPC['jid'];
		$sql = "SELECT a.*,m.prize_type FROM ".tablename('sudo_ku_user_award')." a,".tablename('sudo_ku_prize')." m WHERE m.prize_id=a.prize_id AND a.openid=:openid AND a.jid=:jid LIMIT ".($page-1)*$length.",".$length;
		$res = pdo_fetchall($sql,array(':openid'=>$_W['openid'],':jid'=>$jid));
		
		echo json_encode($res);
		die;
	 }
	 
	/**
     *分享朋友圈来领取奖品                                            
	 *
	 */
	public function doMobileShare(){
		global $_W,$_GPC;

		$this->checkmobile();
		$jid = $_GPC['jid'];
		$res_award = CRUD::findListEx(CRUD::$table_sudo_ku_user_award,'prize_id,id',array(':jid' => $jid,':openid'=>$_W['openid'],':status'=>0));    //中奖表结果
		
		$res_prize = CRUD::findListEx(CRUD::$table_sudo_ku_prize,'prize_type,prize_name',array(':prize_id' => $res_award[0]['prize_id']));                                                                                         //奖品
		if(empty($res_prize)){
			$this->sendJson(1,'服务器繁忙');	
		}
		$count = CRUD::findCount(CRUD::$table_sudo_ku_share,array(':openid'=>$_W['openid'],':jid'=>$jid));
		if((int)$count>0){
			$this->sendJson(2,'您已经分享过了');	
		}
		$data_insert = array(                                                              //插入分享记录
					'status' => $_GPC['status'],
					'jid' => $jid,
					'openid' => $_W['openid'],
					'createtime'=>time()
		);
		
		$res_insert = pdo_insert('sudo_ku_share',$data_insert);   
		if(empty($res_insert)){
			$this->sendJson(1,'服务器繁忙');	
		}
		    
		$return_data = array();                                                           //给前端返回数据
		$return_data['prize_type'] = $res_prize[0]['prize_type'];             
		$return_data['award_id'] = $res_award[0]['id'];
		$return_data['prize_name'] = $res_prize[0]['prize_name'];
		

		$this->sendJson(0,'分享成功',$return_data);                                                                       		              
	}
	
	public function sendJson($code,$msg,$data = array()){
		$json = array();
		$json['respCode'] = $code;
		$json['respMsg'] = $msg;
		$json['data'] = $data;
		echo json_encode($json);
		die;
	} 
	
    /**
     * 活动管理
	 *
     */
     public function  doWebJggManage(){

        global $_W,$_GPC;
	
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {

             $pindex = max(1, intval($_GPC['page']));
             $psize = 20;
             $list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sudo_ku) . " WHERE weid =:weid  ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $this->weid));
             $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sudo_ku) . " WHERE weid =:weid ", array(':weid' => $this->weid));
             $pager = pagination($total, $pindex, $psize);
        } else if ($operation == 'delete') {
             $id = $_GPC['id'];
             pdo_delete(CRUD::$table_sudo_ku, array("id" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_prize, array("jid" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_share, array("jid" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_user, array("jid" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_user_award, array("jid" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_user_record, array("jid" => $id));
			 pdo_delete(CRUD::$table_sudo_ku_user_award, array("jid" => $id));
             message('删除成功！', referer(), 'success');
        }
		
        include $this->template("jgg_manage");
     }
	
	/**
 	 *删除奖品
	 *
	 */
	public function doWebDeletePrize(){
		global $_W,$_GPC;
		$prize_id = $_GPC['prizeId'];
		pdo_delete(CRUD::$table_sudo_ku_prize, array("prize_id" => $prize_id));
		message('删除成功！', referer(), 'success');
	}
	
    /**
     * 参与用户
	 *
     */
    public function  doWebPlay_user(){
        global $_W,$_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            $jid=$_GPC['jid'];
            $jgg=CRUD::findById(CRUD::$table_sudo_ku,$jid);
            if(empty($jgg)){
                message("九宫格活动删除或不存在");
            }
            $itemid=$_GPC['itemid'];
            $keyword=$_GPC['keyword'];
            $where = '';
            $params = array(
                ':jid' => $jid
            );
            if(!empty($keyword)){
                $where .= ' and nickname like :nickname';
                $params[':nickname']="%$keyword%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sudo_ku_user) ." WHERE jid =:jid ".$where."  ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sudo_ku_user) . " WHERE jid =:jid  ".$where, $params);
            $pager = pagination($total, $pindex, $psize);
			
        } else if ($operation == 'delete') {
            $id = $_GPC['id'];
			$openid = pdo_getcolumn('sudo_ku_user',array('id' =>$id),'openid',1);
			
            pdo_delete(CRUD::$table_sudo_ku_user_record, array("openid" => $openid));
            pdo_delete(CRUD::$table_sudo_ku_user_award, array("openid" => $openid));
            pdo_delete(CRUD::$table_sudo_ku_user, array("id" => $id));
            message('删除成功！', referer(), 'success');
        }
        include $this->template("user_list");
    }

    /**
     *  抽奖记录
	 *
     */
    public function  doWebRecordList(){
        global $_W,$_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $jid=$_GPC['jid'];
        $uid=$_GPC['uid'];
		$keyword = $_GPC['keywords'];
		$params[':jid'] = $jid;
		
        if($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
			$sql ="SELECT r.*,u.nickname,p.prize_level FROM " .
				 tablename(CRUD::$table_sudo_ku_user_record) . " r 
				left join ".tablename(CRUD::$table_sudo_ku_user)." u on r.openid=u.openid 
				left join ".tablename(CRUD::$table_sudo_ku_prize)." p on p.prize_id=r.prize_id 
				WHERE r.jid =:jid";
			$where = '';
			if(!empty($keyword)) {
				$where .= ' and u.nickname like :nickname';
				$params[':nickname'] = "%$keyword%";
			}
			if(!empty($uid)){
				$where .=' and u.id=:uid';
				$params[':uid'] = $uid;
			}
            $list = pdo_fetchall($sql.$where." ORDER BY  r.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,$params);
			
			$sql='SELECT COUNT(*) FROM ' .
			   tablename(CRUD::$table_sudo_ku_user_record)." r 
			left join ".tablename(CRUD::$table_sudo_ku_user)." u  
			on r.openid=u.openid WHERE r.jid =:jid " .$where;
            $total = pdo_fetchcolumn($sql, $params);
			
            $pager = pagination($total, $pindex, $psize);
		     
        } elseif ($operation == 'delete') {
            $id = $_GPC['id'];
            pdo_delete(CRUD::$table_sudo_ku_user_record, array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
        load()->func('tpl');
        include $this->template('record_list');
    }

    /**
     * 中奖记录
	 *
     */
    public  function  doWebAwardList(){
        global $_W,$_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        $uid = $_GPC['uid'];
		$keywords= $_GPC['keywords'];
		$jid = $_GPC['jid'];
        $params[':jid'] = $jid;

        $status=-1;
        if(!empty($_GPC['status'])){
            $status=$_GPC['status'];
        }
		
        if($status==2){
			$where .= ' and a.status=0 and p.prize_type=0';
		}
		if($status==1){
			$where .= ' and (( a.status=1 and p.prize_type=0) or p.prize_type!=0)';
		}

        if(!empty($uid)){
			$where .= ' and u.openid = a.openid and u.id=:id';
			$params[':id'] = $uid; 
        }
        if (!empty($keywords)) {
            $where .= ' and u.nickname like :nickname';
            $params[':nickname'] = "%$keywords%";
        }

        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
			$sql = "SELECT a.*,u.nickname,p.prize_level,p.prize_type FROM ".tablename(CRUD::$table_sudo_ku_user_award)." a 
			left join ".tablename(CRUD::$table_sudo_ku_user)." u on a.openid=u.openid  
			left join ".tablename(CRUD::$table_sudo_ku_prize)." p on p.prize_id=a.prize_id 
			WHERE a.jid =:jid ";
			
            $list = pdo_fetchall($sql.$where." ORDER BY  a.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
			
			$sql = 'SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sudo_ku_user_award) . " a 
			left join ".tablename(CRUD::$table_sudo_ku_user)." u on a.openid=u.openid  
			left join ".tablename(CRUD::$table_sudo_ku_prize)." p on p.prize_id=a.prize_id 
			WHERE a.jid =:jid  ";
            $total = pdo_fetchcolumn($sql.$where, $params);
			
            $pager = pagination($total, $pindex, $psize);

        } elseif ($operation == 'delete') {
            $id = $_GPC['id'];
            pdo_delete(CRUD::$table_sudo_ku_user_award, array('id' => $id));
            message('删除成功！', referer(), 'success');

        }elseif($operation=='fj'){

            $id = $_GPC['id'];
            CRUD::updateById(CRUD::$table_sudo_ku_user_award,array('status'=>1),$id);

            message('发奖处理成功！', referer(), 'success');
        }
		
        load()->func('tpl');
        include $this->template('award_list');
    }

    /**
     * 记录导出
	 *
     */
    public function  doWebRdownload(){
        require_once 'rdownload.php';
    }

    /**
     * 用户数据导出
	 *
     */
    public function  doWebUdownload(){
        require_once 'udownload.php';
    }

    /**
     * 中奖数据导出
	 *
     */
    public function  doWebAdownload(){
        require_once 'adownload.php';
    }

	public  function getpicurl($url){
        global $_W;
        return $_W ['attachurl'] . $url;
    }
	
	/**
     * 抽奖并加入数据表中
     *
	 */
    public function  createPlayRecored($jgg,$openid){
	
		$prize=CRUD::findPrizeByJid(CRUD::$table_sudo_ku_prize,$jgg['id']);
		$res=$prize;
		/**
		 *$prize是根据概率所算，得到抽中的奖品id
		 *
		 */
		 $proArr = array(
            $prize[0]['prize_id'] => $prize[0]['possibility'],
            $prize[1]['prize_id'] => $prize[1]['possibility'],
            $prize[2]['prize_id'] => $prize[2]['possibility'],
            $prize[3]['prize_id'] => $prize[3]['possibility'],
            $prize[4]['prize_id'] => $prize[4]['possibility'],
            $prize[5]['prize_id'] => $prize[5]['possibility'],
            $prize[6]['prize_id'] => $prize[6]['possibility'],
            $prize[7]['prize_id'] => $prize[7]['possibility']
        );
        $prize = $this->get_rand($proArr);
       /* $user_ward_count = $this->findUserAwardCount($openid,$jgg['id']);
        if($user_ward_count >= $jgg['per_user_prize_num']) {
            $prize = 0;
        }*/
		
		pdo_begin();
		 
        if($prize != 0){
            $prizeInfo=$this->findPrizeInfo($jgg['id'],$prize);
            if($prizeInfo['prize_left'] <= 0){
                $prize=0;
            }
        }
		
		if($jgg['set_win_one_cash']==1){
			if($prizeInfo['prize_type']==3 || $prizeInfo['prize_type']==4){//只能中一次红包奖品，
				$prize=$this->setWinCash($openid,$jgg['id'],$proArr,$res);
				
				$prizeInfo=$this->findPrizeInfo($jgg['id'],$prize);
				if($prizeInfo['prize_left'] <= 0){
					$prize=0;
				}
			}
		}
		
        switch($prize){
            case $res[0]['prize_id']:
                $award_name=$res[0]['prize_name'];
                break;
            case $res[1]['prize_id']:
                $award_name=$res[1]['prize_name'];
                break;
            case $res[2]['prize_id']:
                $award_name=$res[2]['prize_name'];
                break;
            case $res[3]['prize_id']:
                $award_name=$res[3]['prize_name'];
                break;
            case $res[4]['prize_id']:
                $award_name=$res[4]['prize_name'];
                break;
            case $res[5]['prize_id']:
                $award_name=$res[5]['prize_name'];
                break;
            case $res[6]['prize_id']:
                $award_name=$res[6]['prize_name'];
                break;
			case $res[7]['prize_id']:
                $award_name=$res[7]['prize_name'];
                break;
        }
		$setPrizeInfo = $this->setUserPrize($openid,$jgg,$res); //指定第几次获得什么奖品

		if(!empty($setPrizeInfo)){
				$prizeInfo = $setPrizeInfo;
				$prize = $prizeInfo['prize_id'];
				$award_name = $prizeInfo['prize_name'];
		}
         $recordData=array(
             'jid'=>$jgg['id'],
             'openid'=>$openid,
             'prize_id'=>$prize,
             'award_name'=>$award_name,
             'createtime'=>TIMESTAMP
         );
		
        $res_add_record=CRUD::create(CRUD::$table_sudo_ku_user_record,$recordData);
		
		$type = -1;                         //奖品类型
		$user_award_id = -1;          // 获得奖项的id
        if($prize!=0){
			$value=$this->getRealValue($prizeInfo);
            $award_record=array(
                   'jid'=>$jgg['id'],
                   'openid'=>$openid,
                   'award_name'=>$award_name,
                   'status'=>Sudo_KuModuleSite::$STATUS_UNDO,
                   'prize_id'=>$prize,
				   'prize_value' =>$value,
                   'createtime'=>TIMESTAMP
            );
            $res_add_user_award=CRUD::create(CRUD::$table_sudo_ku_user_award,$award_record);
			$user_award_id = pdo_insertid();
			$res_up_jgg_prize=$this->updatePrizeLeft($prize);
			
			if($prizeInfo['prize_type']==1 || $prizeInfo['prize_type']==2){    //如果中的奖是金币
				
				$res_up_award=pdo_update('sudo_ku_user_award',array('status'=>1),array('jid'=>$jgg['id'],'openid'=>$openid,'prize_id' => $prize));
				$res_up_jgg_user=$this->upCoinLeftTime($value,$jgg['id'],$openid);
					
			}elseif($prizeInfo['prize_type']==3 || $prizeInfo['prize_type']==4){   //如果中的奖是红包
			
				$res_up_award = true;
				$res_up_jgg_user=$this->updateLeftTime($jgg['id'],$openid,1);			
			}else{
			
				$res_up_award = true;
				$res_up_jgg_user = true;
				if($prizeInfo['prize_type']!=5){
					$res_up_jgg_user=$this->updateLeftTime($jgg['id'],$openid,0);
				}
			}
			$this->judge_res($prize,$res_add_record,$res_add_user_award,$res_up_jgg_prize,$res_up_jgg_user,$res_up_award);
			$type = $prizeInfo['prize_type'];
        }else{
			$res_up_jgg_user=$this->updateLeftTime($jgg['id'],$openid,0);
			if(empty($res_add_record) || empty($res_up_jgg_user)){
				pdo_rollback();
				$prize=-1;
			}
		}
		if($prize!=-1){
			pdo_commit();
		}
        return array('prize' =>$prize,'prize_type' =>$type,'user_award_id' => $user_award_id);
    }
	
	/**
     * 企业付款
	 *
     */
    public function  send_qyfk($money,$openid,$desc){
        global $_GPC,$_W;
		
		$setting = $this->findSettings($_W['uniacid'],$_W['current_module']['name']);
		$data=array();
		$data=array(
				'mch_appid'       => $setting['appid'],
				'mchid'        => $setting['mchid'],
				'nonce_str'    => random(32),
				'partner_trade_no'   => random(10).date('Ymd').random(3),				
				'openid'    => $openid,
				'check_name' =>'NO_CHECK',
				'amount' => $money*100,
				'desc'        => $desc,
				'spbill_create_ip'       => $setting['ip'],
		);
		$sign=arr_to_string($data,$setting['password']);	
		$data['sign']=$sign;
		$xml=arr_to_xml($data);
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		
		$extras=array();	
		$extras['CURLOPT_CAINFO']= $this->getPath().'/attachment/red/rootca.pem';
        $extras['CURLOPT_SSLCERT'] = $this->getPath().'/attachment/red/apiclient_cert.pem';
        $extras['CURLOPT_SSLKEY'] = $this->getPath().'/attachment/red/apiclient_key.pem';
		
		load()->func('communication');
		$resp=ihttp_request($url, $xml, $extras);
		
		$array_data = ihttp_response_parse($resp);	
		
		$res=array();
		if($array_data['meta']['code']==200){
			libxml_disable_entity_loader(true);   //禁止引入外部xml实体
			$result = json_decode(json_encode(simplexml_load_string($array_data['meta']['content'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
			if($this->judge($result['return_code'],$result['result_code'])){
				$res['code'] = 0;
				$res['msg'] = '发放红包成功';
			}else{
				$res['code'] = -2;
				$res['msg'] = '发放红包失败';
			}
		}else{
			$res['code']=-1;
			$res['msg']='传输失败';
		}
		return $res;
    }
 //=====================查询数据库信息====================================//
	/**
     * 查找某个奖品剩余数量
	 *
     */
    public function  findPrizeInfo($jid,$prize_id){
 
        $res = pdo_fetch("SELECT * FROM ".tablename(CRUD::$table_sudo_ku_prize)." WHERE prize_id=:prize_id AND jid=:jid FOR UPDATE",array(":prize_id" => $prize_id,":jid" => $jid));
        return $res;
    }
	
	public function getUsers($jid,$openid){
		$res = CRUD::findList(CRUD::$table_sudo_ku_user,array(':jid'=>$jid,':openid'=>$openid));
		return $res[0];
	}

	public function get_pri_award_res($openid,$award_id){
		$sql="SELECT ua.prize_value,ua.openid,ua.createtime FROM ".tablename('sudo_ku_user_award')." ua,".tablename('sudo_ku_prize')." jp WHERE ua.prize_id=jp.prize_id AND (jp.prize_type=3 OR jp.prize_type=4) AND openid=:openid AND ua.id=:id";
		return pdo_fetch($sql,array(':openid' => $openid,':id' => $award_id));
	}
	
	public function findPlayUser($jid,$openid){
       $leftPlayTimes= CRUD::findListEx(CRUD::$table_sudo_ku_user,'leftPlayTimes',array(':jid'=>$jid,':openid'=>$openid));
        return $leftPlayTimes;
    }
	
	public function findSettings($uniacid,$moduleName){
		$res=CRUD::findListEx(CRUD::$table_uni_account_modules,'settings',array(':uniacid'=>$uniacid,':module'=>$moduleName));
		$res=unserialize($res[0]['settings']);
		return $res;
	}
 
 //=====================操作数据库====================================//
	/**
	 *把用户每天的抽奖机会清零
	 *
	 */
	public function updatePlayUser($jid,$openid,$peruserday_num_times){
		$recordCount = CRUD::findDayCount(CRUD::$table_sudo_ku_user_record,array(':jid'=>$jid,':openid'=>$openid));
		$res_up_user = true;
		if($recordCount==0){
			$data = array();
			$data['leftPlayTimes'] = $peruserday_num_times;
			$res_up_user = CRUD::update(CRUD::$table_sudo_ku_user,$data,array('jid'=>$jid,'openid'=>$openid));
		}
		return $res_up_user;
	}
	
	public function judge_res(&$prize,$res1,$res2,$res3,$res4){
		if(empty($res1) || empty($res2) || empty($res3) || empty($res4)){
			pdo_rollback();
			$prize=-1;
			return true;
		}
		return false;
	}
	
	/**
	 *通过奖品的类型得到它的值
	 *
	 *@param array $data 奖品的信息
	 */
	 public function getRealValue($data){
		switch($data['prize_type']){
			case 1:
				$value = $data['prize_value'];
				break;
			case 2:
				$value = mt_rand($data['prize_min_value'],$data['prize_max_value']);
				break;
			case 3:
				$value = $data['prize_value'];
				break;
			case 4:
				$value = mt_rand($data['prize_min_value']*100,$data['prize_max_value']*100)/100;
				$value  = sprintf("%.2f",$value);
				break;
			default:
				$value =0;
				break;
		}
		return $value;
	 }
	 
	public function getCashId($prize){
		$arr = array();
		foreach($prize as $key =>$val){
			if($val['prize_type']==3 || $val['prize_type']==4){
				$arr[] = $val['prize_id'];
			}
		}
		return $arr;
	}
	
    public function getUserInfo($openid)
    {

        if (!empty($openid)) {

            load()->model('account');
            $token = WeAccount::token(WeAccount::TYPE_WEIXIN);
            if (empty($token)) {
                message("获取accessToken失败");
            }
            $userInfo = $this->oauth->getUserInfo($token, $openid);
            $cookie = array();
            $cookie['openid'] = $openid;
            if (!empty($userInfo)) {
                $cookie['nickname'] = $userInfo['nickname'];
                $cookie['headimgurl'] = $userInfo['headimgurl'];
            }
            $session = base64_encode(json_encode($cookie));
            isetcookie('__monjgguser', $session, 24 * 3600 * 365);		
            return $userInfo;
        }
    }

	/**
	 *指定用户中什么奖
	 *$prize array 活动的奖品设置 
	 *
	 */
	public function setUserPrize($openid,$jgg,$prize){
		$prizeName = explode(';',$jgg['setPrizeName']);
		$arr = array();
		foreach($prizeName  as $key =>$val){
			$detailPrize = explode(',',$val);
			$arr[$detailPrize[0]]=$detailPrize[1];
		}
		
		$res_record = CRUD::findDayCount(CRUD::$table_sudo_ku_user_record,array(':jid'=>$jgg['id'],':openid'=>$openid));
		$name = empty($arr[$res_record+1]) ? '':$arr[$res_record+1];
		foreach($prize as $key=>$val){
			if($val['prize_name'] == $name){
				return $val;
			}
		}
		return '';
	}
	
    public function  getClientUserInfo(){
        global $_GPC;
        $session = json_decode(base64_decode($_GPC['__monjgguser']), true);
        return $session;
    }
		
	 /**
     * 判断是否属于地区内
     *@param array $address 允许的地区
	 *@param array $arr        当前IP定位地址
	 *
     */
    public function  isPermitAddress($address,$arr){
		global $_W;
        switch(count($arr)){
				case 3:
					$city = $arr[2];
					break;
				case 2:
					$city= $arr[1];
					break;
				case 1:
					$tips = '您的活动地区不符';
					include $this->template('tips');die;
				default:
					$tips = '您的活动地区不符';
					include $this->template('tips');die;
			}
			if(!in_array($city,$address)){
				$tips = '您的活动地区不符';
				include $this->template('tips');die;
			}
    }
	
	public function judge($code,$result){
		return strtolower($code) == 'success' && strtolower($result) == 'success';
	}
	
	public function setWinCash($openid,$jid,$proArr,$prize){
		$prize_cash = $this->getCashId($prize);
		
		$res = CRUD::findListEx(CRUD::$table_sudo_ku_user,'is_win_cash',array(':openid'=>$openid,':jid'=>$jid));
		
		if($res[0]['is_win_cash']==1){
			foreach($prize_cash as $key=>$val){
				foreach($proArr as $k=>$v){
					if($val ==$k){
						unset($proArr[$k]);
					}
				}
			}
		}
		
		$prize = $this->get_rand($proArr);
		return $prize;
	}
	
	public function updateLeftTime($jid,$openid,$status){
		if($status==1){
			$condition = " SET leftPlayTimes=leftPlayTimes-1,is_win_cash=1";
		}else{
			$condition = " SET leftPlayTimes=leftPlayTimes-1";
		}
		$sql = "UPDATE ".tablename("sudo_ku_user").$condition." WHERE jid=:jid and openid=:openid";
		return pdo_query($sql,array(':jid'=>$jid,':openid'=>$openid));
	}
	
	public function upCoinLeftTime($value,$jid,$openid){
		
		$sql = "UPDATE ".tablename("sudo_ku_user")." SET coin=coin+".$value.",leftPlayTimes=leftPlayTimes-1 WHERE jid=:jid and openid=:openid";
		return pdo_query($sql,array(':jid'=>$jid,':openid'=>$openid));		
	}
	
	public function updatePrizeLeft($prize){
		$sql="UPDATE ".tablename("sudo_ku_prize")." SET prize_left=prize_left-1 WHERE prize_id=:prize";
		return pdo_query($sql,array(':prize' => $prize));
	}
	
	public function getInfo($openid){
		$account_api = WeAccount::create();
		$token = $account_api->getAccessToken();
		if(is_error($token)){
			message('服务繁忙，请重新进入');die;
		}
		$res_user=json_decode(get_fan_info($token,$openid),true);
		return $res_user;
	}
 //===========================处理函数====================================//
    /**
     *取出默认图片
	 *
     */
    public function p_img($index){
      $imgName="p".$index.".png";
        return SUDO_KU_RES . "images/" . $imgName;
    }
	
	public function  checkmobile(){
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
		
            echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
            exit();
        }
    }
	
	public function getPath(){
		$path = dirname(dirname(dirname(__FILE__)));
		return $path;
	}
	
	/**
     * 概率计算
     *
     * @param unknown $proArr
     * @return Ambigous <string, unknown>
     */
    function get_rand($proArr){
        $result = '';
        // 概率数组的总概率精度
		 
        $proSum = array_sum($proArr);
        // 概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum); // 抽取随机数
            if ($randNum <= $proCur) {
                $result = $key; // 得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }
}
<?php
/**
 *
 *
 * @url
 */
defined('IN_IA') or exit('Access Denied');

define("SUDO_KU", "sudo_ku");
define("SUDO_KU_RES", "../addons/" . SUDO_KU . "/");
require_once IA_ROOT . "/addons/" . SUDO_KU . "/CRUD.class.php";

class Sudo_KUModule extends WeModule
{

    public $weid;

    public function __construct()
    {
        global $_W;
        $this->weid = IMS_VERSION < 0.6 ? $_W['weid'] : $_W['uniacid'];
    }
	
	/**
     *点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 
	 *结构为数组。这个参数系统针对不同公众账号独立保存。
	 *在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
	 *
	 */
	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		load()->func('tpl');
		
		if(checksubmit()) {    
            load()->func('file');
			$_W['uploadsetting'] = array();
			$_W['uploadsetting']['image']['folder'] = 'images/' . $_W['uniacid'];
			$_W['setting']['upload']['image']=array_merge($_W['setting']['upload']['image'],array("pem"));
			$_W['setting']['upload']['image']['extentions'] = array_merge($_W['setting']['upload']['image']['extentions'],array("pem"));
			$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		    $md5=md5("{$_W['uniacid']}{$_W['config']['setting']['authkey']}");  
				
		    if (!empty($_FILES['apiclient_cert_file']['name'])) {
	          $file = file_upload($_FILES['apiclient_cert_file']);		 
			  if (is_error($file)) {
				message('apiclient_cert证书保存失败, 请保证目录可写'. $file['message']);
		      } else {
				$_GPC['apiclient_cert']= empty($file['path'])? trim($_GPC['apiclient_cert']):$file['path'];
			  }
			 
		    } 
		    
		    if (!empty($_FILES['apiclient_key_file']['name'])) {
	          $file = file_upload($_FILES['apiclient_key_file']);
			  if (is_error($file)) {
				message('apiclient_key证书保存失败, 请保证目录可写'. $file['message']);
		      } else {
				$_GPC['apiclient_key']= empty($file['path'])? trim($_GPC['apiclient_key']):$file['path'];
			  }
		    } 
		    
		    if (!empty($_FILES['rootca_file']['name'])) {
	          $file = file_upload($_FILES['rootca_file']);
			  if (is_error($file)) {
				message('rootca证书保存失败, 请保证目录可写'. $file['message']);
		      } else {
				$_GPC['rootca']= empty($file['path'])? trim($_GPC['rootca']):$file['path'];
			  }
		    } 	
			 	
            $input =array();
            $input['apiclient_cert'] = trim($_GPC['apiclient_cert']);
            $input['apiclient_key'] = trim($_GPC['apiclient_key']);  
            $input['rootca'] = trim($_GPC['rootca']); 
			
            $input['appid'] = trim($_GPC['appid']);
            $input['secret'] = trim($_GPC['secret']);
            $input['mchid'] = trim($_GPC['mchid']);
            $input['password'] = trim($_GPC['password']);
            $input['ip'] = trim($_GPC['ip']);
                         
            if($this->saveSettings($input)) {
                message('保存参数成功', 'refresh');
            }
        } 
        if(empty($settings['ip'])) {
            $settings['ip'] = $_SERVER['SERVER_ADDR'];
        }
        include $this->template('setting');
	}
	
    public function fieldsFormDisplay($rid = 0)
    {
        global $_W;
		
        if (!empty($rid)) {
            $reply = CRUD::findList(CRUD::$table_sudo_ku, array(":rid" => $rid));
			$reply = $reply[0];
            
			
			$prize = CRUD::findList(CRUD::$table_sudo_ku_prize, array(":jid" => $reply['id']));
			
        }
		$reply['starttime'] = empty($reply['starttime']) ? strtotime(date('Y-m-d H:i')) : $reply['starttime'];
		$reply['endtime'] = empty($reply['endtime']) ? strtotime("+1 week") : $reply['endtime'];
        load()->func('tpl');
		
        include $this->template('form');
    }

    public function fieldsFormValidate($rid = 0){
        global $_GPC, $_W;
		
        if(count($_GPC['prize_type'])!=8){
			message('奖品需满足8个(温馨提示：需为每个奖品设置奖品类型！)');
		}
		
		$sum=$this->verifyPossible($_GPC['possibility']);
		if($sum>100){
			message('概率总和不能大于100');
		}
		if($sum<100){
			message('概率总和不能小于100');
		}
    }

    public function fieldsFormSubmit($rid){
        global $_GPC, $_W;
		$jid = $_GPC['jid'];
		
		
        $data = array(
            'rid' => $rid,
            'weid' => $this->weid,
            'title' => $_GPC['title'],
            'starttime' => strtotime($_GPC['datelimit']['start']),
            'endtime' => strtotime($_GPC['datelimit']['end']),
			'perssion_city' => $_GPC['city'],
			'start_picurl' => $_GPC['start_picurl'],
			'isSubscribe' => $_GPC['issubscribe'],
			'subscribe_tips'=>$_GPC['subscribe_tips'],
			'set_win_one_cash' =>$_GPC['set_win_one_cash'],
			'setPrizeName' => $_GPC['setPrizeName'],
			'male_qrcode' => $_GPC['male_qrcode'],
			'female_qrcode'  => $_GPC['female_qrcode'],
			'tip_img' => $_GPC['tip_img'],
            'rule' => $_GPC['rule'],
			'description' => $_GPC['description'],
            'copyright' => $_GPC['copyright'],
            'peruserday_num_times' => $_GPC['peruserday_num_times'],
            'peruserday_num_times_tips' => $_GPC['peruserday_num_times_tips'],
			'per_nums_value' => $_GPC['per_nums_value'],
			'share_open_close' => $_GPC['share_open_close'],
            'share_title' => $_GPC['share_title'],
			'share_type' => $_GPC['share_type'],
            'share_icon' => $_GPC['share_img'],
			'share_give_num' => $_GPC['give_num'],
			'share_per_day_time_line_num' =>$_GPC['share_per_day_time_line_num'],
			'share_per_day_app_num' => $_GPC['share_per_day_app_num'],
            'share_content' => $_GPC['share_desc'],
            'share_confirm_url' => $_GPC['share_confirm_url'],
			'share_scs_tips' => $_GPC['share_scs_tips'],
            'createtime' => TIMESTAMP
        );
		if(empty($_GPC['jid'])){
			pdo_begin();
		}
		if(empty($_GPC['jid'])){
            $res=CRUD::create(CRUD::$table_sudo_ku, $data);
			$jid=pdo_insertid();
        }else{
            $res=CRUD::updateById(CRUD::$table_sudo_ku, $data, $jid);
        }
		foreach($_GPC['prize_type'] as $key =>$val){
			$data=array();
			$data['jid']=$jid;
			$data['prize_value'] = $_GPC['prize_value'][$key];
			$data['location'] = $_GPC['location'][$key];
			$data['jid_loca'] = $jid.'_'.$_GPC['location'][$key];
			$data['prize_level'] = $_GPC['prize_level'][$key];
			$data['prize_total'] = $_GPC['prize_total'][$key];
			$data['prize_left'] = $_GPC['prize_total'][$key];
			$data['prize_name'] = $_GPC['prize_name'][$key];
			$data['label_name'] = $_GPC['label_name'][$key];
			$data['desc'] = $_GPC['desc'][$key];
			$data['sign_decrypt'] = $_GPC['sign_decrypt'][$key];
			$data['possibility'] = $_GPC['possibility'][$key];
			$data['win_max_sum'] = $_GPC['win_max_sum'][$key];
			$data['give_max_sum'] = $_GPC['give_max_sum'][$key];
			$data['prize_min_value'] = $_GPC['prize_min_value'][$key];
			$data['prize_max_value'] = $_GPC['prize_max_value'][$key];
			
			if($_GPC['jid']){
				$data['prize_img'] = $_GPC['prize_img'][$_GPC['prize_id'][$key]];
			}else{
				$data['prize_img'] = $_GPC['prize_img'][$_GPC['prize_id'][$key]];
			}
			$data['prize_type'] = $_GPC['prize_type'][$key];
			if(empty($_GPC['jid'])){
				$result=CRUD::create(CRUD::$table_sudo_ku_prize, $data);
			}else{			
				$result=CRUD::update(CRUD::$table_sudo_ku_prize, $data, array('prize_id' => intval($_GPC['prize_id'][$key])));
				//$result=pdo_update('sudo_ku_prize',$data,array('prize_id'=>intval($_GPC['prize_id'][$key])));
			}
			if(empty($_GPC['jid'])){
				if(empty($result) || empty($res)){
					pdo_rollback();
					message('添加失败,请重新添加');
					die;
				}
			}
		}
		if(empty($_GPC['jid'])){
			pdo_commit();
		}
		return true;    
    }

    public function ruleDeleted($rid){
        $jgg = CRUD::findUnique(CRUD::$table_sudo_ku, array(":rid" => $rid));
        pdo_delete(CRUD::$table_sudo_ku, array("id" => $jgg['id']));
    }

    public function p_img($index){
        $imgName = "p" . $index . ".png";
        return SUDO_KU_RES . "images/" . $imgName;
    }
	
    public  function getItemBg($bgColor){
        if (empty($bgColor)){
            return "#DABB82";
        }
        return $bgColor;
    }

	public function verifyPossible($param){
		$sum = 0;
		unset($param['change_id']);
		foreach($param as $key => $val){
			$sum +=$val;
		}
		return $sum;
	}
}
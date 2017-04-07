<?php
	
	/**
	 *静默获取用户信息
	 *
	 */
	function get_url_snsapi_base($appid,$header_url){
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($header_url)."&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
		return $url;
	}
	
	/**
	 *授权获取用户信息
	 *
	 */
	function get_url_snsapi_userinfo($appid,$header_url){
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".urlencode($header_url)."&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
		return $url;
	}
	
	function get_openid_by_code($code,$appid,$secret){
		$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
		$res =  ihttp_request($url);
		return $res['content'];
	}
	
	function get_fans($access_token,$openid){
		$url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&next_openid=".$openid;
		$res=ihttp_request($url);
		return $res['content'];
	}
	
	function get_fan_info($access_token,$openid){
		$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
		$res=ihttp_request($url);
		return $res['content'];
	}
	
	function sendJson($code,$msg){
		 $data['code'] = $code;
         $data['msg'] = $msg;
		 $data['prize'] = -1;
		 $data['prize_type'] = -1;
         echo json_encode($data);	
         die;
	}
	
	function get_address($ip){
		if($ip == "127.0.0.1"){
			message('不可使用本机地址');die;
		}			
		$api = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip";   
		$json = file_get_contents($api);  
		$arr = json_decode($json,TRUE);   
		$country = $arr['country'];        
		$province = $arr['province'];      
		$city = $arr['city'];               
		if((string)$country == "中国"){  
			if((string)($province) != (string)$city){  
				$_location[] = $country;
				$_location[] = $province;
				$_location[] = $city;
			}else{  
				$_location[] = $country;
                $_location[] = $city;
			}  
		}else{  
			   $_location [] = $country;  
		}
		return $_location;
	}
	
	/**
	 *把数组转换成签名数据
	 *@ return string $sign 返回签名
	 */
	function arr_to_string($data,$key){
		ksort($data,SORT_STRING);
		$str = '';
		foreach($data as $k =>$val){
			$str .=$k.'='.$val.'&';
		}
		$str = trim($str,'&');
		$stringSignTemp = $str.'&key='.$key;
		$sign = strtoupper(md5($stringSignTemp));
		return $sign;
	}
	
	/**
	 *把数组转换成xml数据（企业付款）
	 *@ return string $sign 返回签名
	 */
	function arr_to_xml($data){
		$str = '<xml>';
		foreach($data as $key =>$val){
			$str .='<'.$key.'>'.$val.'</'.$key.'>';
		}
		$str .='</xml>';
		return $str;
	}
?>
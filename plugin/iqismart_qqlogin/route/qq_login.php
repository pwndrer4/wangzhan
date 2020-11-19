<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(APP_PATH.'plugin/iqismart_qqlogin/model/qq_login.func.php');

$action = param(1);
$action2 = param(2);

$return_url = http_url_path().url('qq_login-return');
$register_url = http_url_path().url('qq_login-register');

if(empty($action)) {
	
	$link = qq_login_link($return_url);
	
	http_location($link);

//	- - - - - - - ->QQ登录	Sign in By QQ
} elseif($action == 'toRegister'){
	$link = qq_login_link($register_url);
	
	http_location($link);
}
elseif($action == 'return') {
	
	$qq_login = kv_get('iqismart_qqlogin');
	$appid = $qq_login['appid'];
	$appkey = $qq_login['appkey'];
  	$register = $qq_login['register']; 
  
	$error = param('error');
	$code = param('code');
  	$username = param('username');
  	$avatar = param('avatar');
  	$openid = param('openid');
	if(!$openid) {
		message(-1, '获取 openid 失败，错误原因：'.$error);
	}else{
    	$username = urldecode($username);
  		$avatar = urldecode($avatar);
  		$openid = urldecode($openid);
    }
	// 如果有 openid，则直接自动登陆
	$user = qq_login_read_user_by_openid($openid);
	if(!$user) {
      	if(!$register || $register == 0){
        	message(-1, jump('未开启QQ注册', http_referer(), 2));die;
        }else{
          $user = qq_login_create_user($username, $avatar, $openid);
          $uid = $user['uid'];

          user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

          $_SESSION['uid'] = $uid;
          user_token_set($uid);      	
          message(0, jump('QQ注册成功', '/', 2)); 
        }
	} 
	
	$uid = $user['uid'];

	user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

	$_SESSION['uid'] = $uid;
	user_token_set($uid);

	message(0, jump('登陆成功', '/', 2));

//	- - - - - - - ->QQ注册	Sign up By QQ
}elseif ($action == 'register'){
	$return_url = http_url_path().url('qq_login-register');
	$link = qq_login_link($return_url);

	$qq_login = kv__get('qq_login');
	$register = $qq_login['register'];
	$appid = $qq_login['appid'];
	$appkey = $qq_login['appkey'];
	if ($register == '1'){
		//$state = param('state');
		$code = param('code');

		// token 保存起来，提高速度
		$token = qq_login_get_token($appid, $appkey, $code, $return_url);
		!$token AND message($errno, $errstr);

		// 获取 openid
		$openid = qq_login_get_openid_by_token($token);
		if (!$openid){
			http_location($link);
		}else{
			$qquser = qq_login_get_user_by_openid($openid, $token, $appid);
			$user = qq_login_create_user($qquser['nickname'], $qquser['figureurl_qq_2'], $openid);
			$uid = $user['uid'];

			user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));

			$_SESSION['uid'] = $uid;
			user_token_set($uid);

			message(0, jump('QQ注册成功，请尽快绑定手机号', http_url_path().url('my-mobile'), 2));
		}

	}else{
		message(-1, jump('未开启QQ注册', http_referer(), 2));
	}

//	- - - - - - - ->绑定QQ	Bind QQ
}elseif ($action == 'bind'){
	if($action2 == 'go'){
		if(empty($uid)){
			message(-1, jump('请先登录本站再绑定QQ', http_url_path().url('user-login'), 2));
		}else{
			$return_url = http_url_path().url('qq_login-bind');
			$link = qq_login_bind_link($return_url);
			http_location($link);
		}
	}else{
		if(empty($uid)){
			message(-1, jump('请先登录本站', http_url_path().url('user-login'), 2));
		}else{
			$qq_login = kv_get('iqismart_qqlogin');
            $appid = $qq_login['appid'];
            $appkey = $qq_login['appkey']; 
            $error = param('error');
            $code = param('code');
    
            $openid = param('openid');
            if(!$openid) {
                message(-1, '获取 openid 失败，错误原因：'.$error);
            }else{
                
                $openid = urldecode($openid);
            }
            // 如果有 openid，则直接自动登陆
            $user = qq_login_read_user_by_openid($openid);
			if (!$user){
				$r = qq_login_bind($uid, $openid);
				if($r != -1){
					message(0, jump('QQ绑定成功', http_url_path().url('my'),2));
				}
			}else{
				message(-1, jump('该QQ已绑定本站用户，请更换QQ重试', http_url_path().url('my'),2));
			}
		}
	}

//	- - - - - - - ->取消绑定QQ	Unbind QQ
}elseif ($action == 'unbind'){
	if (empty($uid)){
		message(-1, jump('请先登录本站', http_url_path().url('user-login'), 2));
	}else{
		db_delete('user_open_plat', array('uid'=>$uid));
		message(0, jump('QQ解绑成功', http_url_path().url('my'),2));
	}
}
?>

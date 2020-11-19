<?php
function qq_login_bind_link($return_url) {
	$qqlogin = kv_get('iqismart_qqlogin');
	$appid = $qqlogin['appid'];
	$appkey = $qqlogin['appkey'];
	$return_url = urlencode($return_url);
	
	$scope = "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo";
	$state = md5(uniqid(rand(), TRUE)); //CSRF protection
	$login_url = "https://www.iqismart.com/qq_login-bind-go.htm?appid=$appid&redirect_uri=$return_url&state=$state&scope=$scope";
	return $login_url;
}

function qq_login_link($return_url) {
	$qqlogin = kv_get('iqismart_qqlogin');
	$appid = $qqlogin['appid'];
	$appkey = $qqlogin['appkey'];
	$return_url = urlencode($return_url);
	
	$scope = "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo";
	$state = md5(uniqid(rand(), TRUE)); //CSRF protection
	$login_url = "https://www.iqismart.com/qq_login.htm?appid=$appid&redirect_uri=$return_url&state=$state&scope=$scope";
	return $login_url;
}

// 从本地数据读取
function qq_login_read_user_by_openid($openid) {
	$arr = db_find_one('iqismart_qqlogin', array('openid'=>$openid));
	if($arr) {
		$arr2 = user_read($arr['uid']);
		if($arr2) {
			$arr = array_merge($arr, $arr2);
		} else {
			db_delete('iqismart_qqlogin', array('openid'=>$openid));
			return FALSE;
		}
	}
	return $arr;
}

function qq_login_create_user($username, $avatar_url_2, $openid) {
	global $conf, $time, $longip;
	
	$arr = qq_login_read_user_by_openid($openid);
	if($arr) return xn_error(-2, '已经注册');
	
	// 自动产生一个用户名
	$r = user_read_by_username($username);
	if($r) {
		// 特殊字符过滤
		$username = xn_substr($username.'_'.$time, 0, 31);
		$r = user_read_by_username($username);
		if($r) return xn_error(-1, '用户名被占用。');
	}
	// 自动产生一个 Email
	$email = "qq_$time@qq.com";
	$r = user_read_by_email($email);
	if($r) return xn_error(-1, 'Email 被占用');
	// 随机密码
	$password = md5(rand(100000, 999999).$time);
	$user = array(
		'username'=>$username,
		'email'=>$email,
		'password'=>$password,
		'gid'=>101,
		'salt'=>rand(100000, 999999),
		'create_date'=>$time,
		'create_ip'=>$longip,
		'avatar'=>0,
		'logins' => 1,
		'login_date' => $time,
		'login_ip' => $longip,
	);
	$uid = user_create($user);
	if(empty($uid)) return xn_error(-1, '注册失败');
	
	$user = user_read($uid);

	$r = db_insert('iqismart_qqlogin', array('uid'=>$uid, 'platid'=>1, 'openid'=>$openid));
	if(empty($r)) return xn_error(-1, '注册失败');
	
	runtime_set('users+', '1');
	runtime_set('todayusers+', '1');
	
	// 头像不重要，忽略错误。
	if($avatar_url_2) {
		$filename = "$uid.png";
		$dir = substr(sprintf("%09d", $uid), 0, 3).'/';
		$path = $conf['upload_path'].'avatar/'.$dir;
		!is_dir($path) AND mkdir($path, 0777, TRUE);
		
		$data = file_get_contents($avatar_url_2);
		file_put_contents($path.$filename, $data);
		
		user_update($uid, array('avatar'=>$time));
	}
	return $user;
}

function qq_login_bind($uid,$openid){
	$r = db_insert('iqismart_qqlogin', array('uid'=>$uid, 'platid'=>1, 'openid'=>$openid));
	if(empty($r)){} return xn_error(-1, '绑定失败');
}
?>

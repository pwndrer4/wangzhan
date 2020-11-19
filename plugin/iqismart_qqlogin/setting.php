<?php

/*
	hhj luntan 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-sifoucn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('iqismart_qqlogin');
	
	$input = array();
	$input['appid'] = form_text('appid', $kv['appid']);
	$input['appkey'] = form_text('appkey', $kv['appkey']);
    $input['register'] = form_text('register', $kv['register']);
	
	include _include(APP_PATH.'plugin/iqismart_qqlogin/setting.htm');
	
} else {

	$kv = array(); 
	$kv['appid'] = param('appid');
	$kv['appkey'] = param('appkey');
    $kv['register'] = param('register');

	kv_set('iqismart_qqlogin', $kv);
	
	message(0, '修改成功');
}
	
?>
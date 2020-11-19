<?php

/*
	hhj luntan 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-sifoucn_qq_login.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
	
	$kv = kv_get('iqismart_weixin');
	
	$input = array();
	$input['appid'] = form_text('appid', $kv['appid']);
	$input['appkey'] = form_text('appkey', $kv['appkey']);
  	$input['qrcode_expiry'] = form_text('qrcode_expiry', $kv['qrcode_expiry']);
  	$input['bind_get_credits'] = form_text('bind_get_credits', $kv['bind_get_credits']);
  	$input['bind_get_golds'] = form_text('bind_get_golds', $kv['bind_get_golds']);
  	$input['bind_get_rmbs'] = form_text('bind_get_rmbs', $kv['bind_get_rmbs']);
  	$input['bind_post'] = form_text('bind_post', $kv['bind_post']);
  	$input['wx_notice'] = form_text('wx_notice', $kv['wx_notice']);
  	$input['unbind'] = form_text('unbind', $kv['unbind']);
	 
	include _include(APP_PATH.'plugin/zz_iqismart_weixin/setting.htm');
	
} else {

	$kv = array(); 
	$kv['appid'] = param('appid');
	$kv['appkey'] = param('appkey');
  	$kv['qrcode_expiry'] = param('qrcode_expiry');
    $kv['bind_get_credits'] = param('bind_get_credits');  
  	$kv['bind_get_golds'] = param('bind_get_golds');
  	$kv['bind_get_rmbs'] = param('bind_get_rmbs');
  	$kv['bind_post'] = param('bind_post');
  	$kv['wx_notice'] = param('wx_notice');
  	$kv['unbind'] = param('unbind');

	kv_set('iqismart_weixin', $kv);
	
	message(0, '修改成功');
}
	
?>
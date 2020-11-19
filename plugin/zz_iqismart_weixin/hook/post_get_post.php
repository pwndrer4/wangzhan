$wxlogin = kv_get('iqismart_weixin');
 
$wechat_bind = db_find_one('skiy_wx_login', array('uid' => $user['uid']));
 
if(empty($wechat_bind) && $wxlogin['bind_post'] == 1) {
    message(-1, jump('<div style="text-align: center">必须绑定微信才能发布内容', url('my'),3));
}
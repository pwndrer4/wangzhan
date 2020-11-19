<?php

function send_weixin_notice($to_uid,$first,$msg_url,$type='',$result='',$remark=''){
  	require_once(APP_PATH . 'plugin/zz_iqismart_weixin/model/wx_login.func.php');
   $bind = wx_had_bind_user_by_uid($to_uid);
   if($bind){
    	$openid = $bind['openid'];
		$wxlogin = kv_get('iqismart_weixin');
        $api_key = $wxlogin['appkey'];
        $url = 'https://www.iqismart.com/iqismart_wx_msg.htm?openid='.$openid.'&api_key='.$api_key.'&first='.urldecode($first).'&first='.urldecode($first).'&type='.urldecode($type).'&remark='.urldecode($remark).'&result='.urldecode($result);
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
          curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
          curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
     	echo xn_json_encode($sContent);
        if (intval($aStatus["http_code"]) == 200) {
          return $sContent;
        } else {
          return false;
        }
   }
}
?>
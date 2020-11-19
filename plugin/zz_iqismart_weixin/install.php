<?php

/**
 * 免签微信接入
 * style <364337403@qq.com>
 * https://www.iqismart.com/
 */

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS `{$tablepre}iqismart_wx_login` (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '序号',
    `uid` int(11) NOT NULL COMMENT '用户ID',
    `openid` varchar(64) NOT NULL COMMENT '微信openid',
    `create_date` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='微信登录'";
db_exec($sql);

$sql = "alter table `{$tablepre}user` add column bind_gift int(10) default 0";
db_exec($sql);

$sql = "alter table `{$tablepre}user` add column is_follow int(10) default 0";
db_exec($sql);

// 初始化
$kv = kv_get('iqismart_weixin');
if (empty($kv)) {
	$kv = array('appid'=>'1', 'appkey'=>'iqismart_public_free_license',"qrcode_expiry"=>120,'bind_get_rmbs'=>10,'bind_get_credits'=>10,'bind_get_golds'=>10,'bind_post'=>0,'wx_notice'=>0,'unbind'=>1);
	kv_set('iqismart_weixin', $kv);
}


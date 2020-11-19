<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

user_login_check();


if($action == 'tt_seo') {
    if ($method == 'GET')
        include _include(APP_PATH . 'plugin/tt_seo/view/htm/tt_seo.htm');
    elseif ($method == 'POST') {
        if($gid!=1) die('无权操作');
        $auto_push = param('auto','0');
        $xzh_push = param('xzh','0');
        $auto_push_mip = param('auto_mip','0');
        $xzh_push_mip = param('xzh_mip','0');
        $tid = param('tid','0');
        if($tid=='0')die('ERROR');
        if($auto_push=='auto')
            seo_auto_push(array($tid),$uid,setting_get('tt_seo'));
        if($xzh_push=='xzh')
            seo_xzh_push(array($tid),$uid,setting_get('tt_seo'),'batch');
        if($auto_push_mip=='auto_mip')
            seo_auto_push_mip(array($tid),$uid,setting_get('tt_seo'));
        if($xzh_push_mip=='xzh_mip')
            seo_xzh_push_mip(array($tid),$uid,setting_get('tt_seo'),'batch');
        message(0, '更新成功!');
    }
}


if($action == 'create') {
	
	$tid = param(2);
	$quick = param(3);
	$quotepid = param(4);
		
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists'));
	
	$fid = $thread['fid'];
	
	$forum = forum_read($fid);
	empty($forum) AND message(-1, lang('forum_not_exists'));
	
	$r = forum_access_user($fid, $gid, 'allowpost');
	if(!$r) {
		message(-1, lang('user_group_insufficient_privilege'));
	}
	
	($thread['closed'] && ($gid == 0 || $gid > 5)) AND message(-1, lang('thread_has_already_closed'));
	
	$wxlogin = kv_get('iqismart_weixin');
 
$wechat_bind = db_find_one('skiy_wx_login', array('uid' => $user['uid']));
 
if(empty($wechat_bind) && $wxlogin['bind_post'] == 1) {
    message(-1, jump('<div style="text-align: center">必须绑定微信才能发布内容', url('my'),3));
}
	
	if($method == 'GET') {
		
		
		
		$header['title'] = lang('post_create');
		$header['mobile_title'] = lang('post_create');
		$header['mobile_link'] = url("thread-$tid");

		include _include(APP_PATH.'view/htm/post.htm');
		
	} else {
		
		
		
		$message = param('message', '', FALSE);
		empty($message) AND message('message', lang('please_input_message'));
		
		$doctype = param('doctype', 0);
		xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
		
		$thread['top'] > 0 AND thread_top_cache_delete();
		
		$quotepid = param('quotepid', 0);
		$quotepost = post__read($quotepid);
		(!$quotepost || $quotepost['tid'] != $tid) AND $quotepid = 0;
		
		$post = array(
			'tid'=>$tid,
			'uid'=>$uid,
			'create_date'=>$time,
			'userip'=>$longip,
			'isfirst'=>0,
			'doctype'=>$doctype,
			'quotepid'=>$quotepid,
			'message'=>$message,
		);
		$pid = post_create($post, $fid, $gid);
		empty($pid) AND message(-1, lang('create_post_failed'));
		
		// thread_top_create($fid, $tid);

		$post = post_read($pid);
		$post['floor'] = $thread['posts'] + 2;
		$postlist = array($post);
		
		$allowpost = forum_access_user($fid, $gid, 'allowpost');
		$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
		$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
		
		require_once(APP_PATH . 'plugin/zz_iqismart_weixin/model/wx_notice.func.php');

    /**
     * 提取富文本字符串的纯文本,并进行截取;
     * @param $string 需要进行截取的富文本字符串
     * @param $int 需要截取多少位
     */
     function StringToText($string,$num){
        if($string){
			$string = preg_replace('~<([a-z]+?)\s+?.*?>~i','<$1>',$string); 
            
            //把一些预定义的 HTML 实体转换为字符
            $html_string = htmlspecialchars_decode($string);
            //将空格替换成空
            $content = str_replace(" ", "", $html_string);
            //函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
            $contents = strip_tags($content);
            //返回字符串中的前$num字符串长度的字符
            $res =  mb_strlen($contents,'utf-8') > $num ? mb_substr($contents, 0, $num, "utf-8").'....' : mb_substr($contents, 0, $num, "utf-8");
			$res = str_replace('"',"",$res);  
			//echo '$reply_content='.$res;

			return $res;
        }else{
            return $string;
        }
    }

//获取帖子标题
$thread_msg=thread_read($tid);
$thread_title=$thread_msg['subject'];
$thread_uid=$thread_msg['uid'];//楼主uid
//获取楼主用户名
$thread_user_msg=user_read($thread_uid);
$user_name=$thread_user_msg['username'];
$user_email=$thread_user_msg['email'];
//获取回帖者用户名
$reply_user_msg=user_read($uid);
$reply_nick=$reply_user_msg['username'];
//回复内容
$reply_content=StringToText($message,150);

//回复时间
$reply_time=date("Y/m/d H:i:s",$time);

//帖子链接
$thread_url=$_SERVER['SERVER_NAME'].'/'.url("thread-".$tid);

//判断是否为引用
if($quotepid !=0){
	$quotepost_msg=user_read($quotepost['uid']);
	$user_email=$quotepost_msg['email'];
	$user_name=$quotepost_msg['username'];
 
} 
              
send_weixin_notice($thread_user_msg['uid'], $reply_content,$thread_url,'主题回复',$thread_title,'点击查看');
              
//引用
if($quotepid !=0 && $thread_user_msg['uid'] !=$quotepost_msg['uid'] ){
   send_weixin_notice($quotepost_msg['uid'], $reply_content,$thread_url,'主题回复',$thread_title,'点击查看');           
}
 

 
 
  
		
		// 直接返回帖子的 html
		// return the html string to browser.
		$return_html = param('return_html', 0);
		if($return_html) {
			$filelist = array();
			ob_start();
			include _include(APP_PATH.'view/htm/post_list.inc.htm');
			$s = ob_get_clean();
						
			message(0, $s);
		} else {
			message(0, lang('create_post_sucessfully'));
		}
	
	}
	
} elseif($action == 'update') {

	$pid = param(2);
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists'));
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists'));
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, lang('forum_not_exists'));
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, lang('user_group_insufficient_privilege'));
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
	!$allowupdate AND !$post['allowupdate'] AND message(-1, lang('have_no_privilege_to_update'));
	!$allowupdate AND $thread['closed'] AND message(-1, lang('thread_has_already_closed'));
	
	
	
	if($method == 'GET') {
		
		
		
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		
		// 如果为数据库减肥，则 message 可能会被设置为空。
		// if lost weight for the database, set the message field empty.
		$post['message'] = htmlspecialchars($post['message'] ? $post['message'] : $post['message_fmt']);
		
		($uid != $post['uid']) AND $post['message'] = xn_html_safe($post['message']);
		
		$attachlist = $imagelist = $filelist = array();
		if($post['files']) {
			list($attachlist, $imagelist, $filelist) = attach_find_by_pid($pid);
		}
		
		
		
		include _include(APP_PATH.'view/htm/post.htm');
		
	} elseif($method == 'POST') {
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		$message = param('message', '', FALSE);
		$doctype = param('doctype', 0);
		
		
//message(-1,$_REQUEST['message']);
$n = preg_match_all("/(?:[^\"]|^)(https?\:\/\/[^\x{4e00}-\x{9fa5}\"\s<]+)/u",$message,$result);
if($n>0){
$newm="\${1}<a href=\"\${2}\" target=\"_blank\" _href=\"\${2}\"><span style=\"color:#0070c0\">\${2}</span></a>";
$message=preg_replace("/([^\"]|^)(https?\:\/\/[^\x{4e00}-\x{9fa5}\"\s<]+)/u",$newm,$message);
}
		
		empty($message) AND message('message', lang('please_input_message'));
		mb_strlen($message, 'UTF-8') > 2048000 AND message('message', lang('message_too_long'));
		
		$arr = array();
		if($isfirst) {
			$newfid = param('fid');
			$forum = forum_read($newfid);
			empty($forum) AND message('fid', lang('forum_not_exists'));
			
			if($fid != $newfid) {
				!forum_access_user($fid, $gid, 'allowthread') AND message(-1, lang('user_group_insufficient_privilege'));
				$post['uid'] != $uid AND !forum_access_mod($fid, $gid, 'allowupdate') AND message(-1, lang('user_group_insufficient_privilege'));
				$arr['fid'] = $newfid;
			}
			if($subject != $thread['subject']) {
				mb_strlen($subject, 'UTF-8') > 80 AND message('subject', lang('subject_max_length', array('max'=>80)));
				$arr['subject'] = $subject;
			}
			$arr AND thread_update($tid, $arr) === FALSE AND message(-1, lang('update_thread_failed'));
		}
		$r = post_update($pid, array('doctype'=>$doctype, 'message'=>$message));
		$r === FALSE AND message(-1, lang('update_post_failed'));
		
		
		
		message(0, lang('update_successfully'));
		//message(0, array('pid'=>$pid, 'subject'=>$subject, 'message'=>$message));
	}
	
} elseif($action == 'delete') {

	$pid = param(2, 0);
	
	
	
	if($method != 'POST') message(-1, lang('method_error'));
	
	$post = post_read($pid);
	empty($post) AND message(-1, lang('post_not_exists'));
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists'));
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, lang('forum_not_exists'));
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, lang('user_group_insufficient_privilege'));
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
	!$allowdelete AND !$post['allowdelete'] AND message(-1, lang('insufficient_delete_privilege'));
	!$allowdelete AND $thread['closed'] AND message(-1, lang('thread_has_already_closed'));
	
	

	if($isfirst) {
		thread_delete($tid);
	} else {
		post_delete($pid);
		//post_list_cache_delete($tid);
	}
	
	
	
	message(0, lang('delete_successfully'));

}



elseif ($action == 'post_like') {

	$header['title'] = "帖子点赞 - " . $conf['sitename'];
	
	if (!$uid) {
		message(0, '只有登录后才能够点赞！');
	}

	$haya_post_like_config = setting_get('haya_post_like');
	
	if ($method == 'POST') {

		$pid = param('pid');

		$post = post_read($pid);
		empty($post) AND message(0, lang('post_not_exists'));

		if ($post['isfirst'] == 1) {
			if (isset($haya_post_like_config['open_thread'])
				&& $haya_post_like_config['open_thread'] != 1
			) {
				message(0, '对帖子点赞功能没有启用！');
			}
		} else {
			if (isset($haya_post_like_config['open_post'])
				&& $haya_post_like_config['open_post'] != 1
			) {
				message(0, '对回帖点赞功能没有启用！');
			}
		}
		
		$haya_post_like_check = haya_post_like_find_by_uid_and_pid($uid, $pid);
		
		$action2 = param(2, 'create');
		if ($action2 == 'create') {
			if (!empty($haya_post_like_check)) {
				message(0, '你已经点赞过该回帖！');
			}
			
			haya_post_like_create(array(
				'tid' => $post['tid'], 
				'pid' => $pid, 
				'uid' => $user['uid'],
				'create_date' => time(),
				'create_ip' => $longip,
			));
			
			haya_post_like_loves($pid, 1);
			
			if (function_exists("notice_send")) {
				notice_send($post['uid'], '<a href="'.url('user-'.$user['uid']).'" target="_blank">'.$user['username'].'</a> 点赞了你的回帖 <a target="_blank" href="'.url('thread-'.$post['tid']).'">'.$post['subject'].'</a>', 4);
			}
			
			$haya_post_like_count = haya_post_like_count(array('pid' => $pid));
			$haya_post_like_msg = array(
				'count' => intval($haya_post_like_count),
				'msg' => '点赞回帖成功！',
			);
			
			message(1, $haya_post_like_msg);
		} elseif ($action2 == 'delete') {
			if (empty($haya_post_like_check)) {
				message(0, '你还没有点赞过该回帖！');
			}
			
			haya_post_like_delete_by_pid_and_uid($pid, $user['uid']);
			
			haya_post_like_loves($pid, -1);
			
			$haya_post_like_count = haya_post_like_count(array('pid' => $pid));
			$haya_post_like_msg = array(
				'count' => intval($haya_post_like_count),
				'msg' => '取消点赞成功！',
			);
			
			message(1, $haya_post_like_msg);
		}
		
		message(1, '访问错误！');	
	}
	
	message(1, '访问错误！');

}




?>
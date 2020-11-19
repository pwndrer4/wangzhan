<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);



// 发表主题帖 | create new thread
if($action == 'create') {
	
	$wxlogin = kv_get('iqismart_weixin');
 
$wechat_bind = db_find_one('skiy_wx_login', array('uid' => $user['uid']));
 
if(empty($wechat_bind) && $wxlogin['bind_post'] == 1) {
    message(-1, jump('<div style="text-align: center">必须绑定微信才能发布内容', url('my'),3));
}
		
	user_login_check();

	if($method == 'GET') {
		
		
		
		$fid = param(2, 0);
		$forum = $fid ? forum_read($fid) : array();
		
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		if(empty($forumlist_allowthread)) {
			message(-1, lang('user_group_insufficient_privilege'));
		}
		
		$header['title'] = lang('create_thread');
		$header['mobile_title'] = $fid ? $forum['name'] : '';
		$header['mobile_linke'] = url("forum-$fid");
		
		
		
		include _include(APP_PATH.'view/htm/post.htm');
		
	} else {
		
		
//message(-1,$_REQUEST['message']);
$n = preg_match_all("/(?:[^\"]|^)(https?\:\/\/[^\x{4e00}-\x{9fa5}\"\s<]+)/u",$_REQUEST['message'],$result);
if($n>0){
$newm="\${1}<a href=\"\${2}\" target=\"_blank\" _href=\"\${2}\"><span style=\"color:#0070c0\">\${2}</span></a>";
$_REQUEST['message']=preg_replace("/([^\"]|^)(https?\:\/\/[^\x{4e00}-\x{9fa5}\"\s<]+)/u",$newm,$_REQUEST['message']);
}
		
		$fid = param('fid', 0);
		$forum = forum_read($fid);
		empty($forum) AND message('fid', lang('forum_not_exists'));
		
		$r = forum_access_user($fid, $gid, 'allowthread');
		!$r AND message(-1, lang('user_group_insufficient_privilege'));
		
		$subject = param('subject');
		empty($subject) AND message('subject', lang('please_input_subject'));
		xn_strlen($subject) > 128 AND message('subject', lang('subject_length_over_limit', array('maxlength'=>128)));
		
		$message = param('message', '', FALSE);
		empty($message) AND message('message', lang('please_input_message'));
		$doctype = param('doctype', 0);
		$doctype > 10 AND message(-1, lang('doc_type_not_supported'));
		xn_strlen($message) > 2028000 AND message('message', lang('message_too_long'));
		
		$thread = array (
			'fid'=>$fid,
			'uid'=>$uid,
			'sid'=>$sid,
			'subject'=>$subject,
			'message'=>$message,
			'time'=>$time,
			'longip'=>$longip,
			'doctype'=>$doctype,
		);
		
		
		
		$tid = thread_create($thread, $pid);
		$pid === FALSE AND message(-1, lang('create_post_failed'));
		$tid === FALSE AND message(-1, lang('create_thread_failed'));
		
		
    if($tid && $forum['allowSEO']=='1' && $group['allowSEO']=='1') {
        $set_seo = setting_get('tt_seo');
        if ($set_seo['auto'] == '1') seo_auto_push(array($tid),$uid,$set_seo);
        if ($set_seo['xzh']=='1') seo_xzh_push(array($tid),$uid,$set_seo,'realtime');
        if ($set_seo['mip_ping']=='1') seo_auto_push_mip(array($tid),$uid,$set_seo);
        if ($set_seo['mip_xzh']=='1') seo_xzh_push_mip(array($tid),$uid,$set_seo);
    }

		message(0, lang('create_thread_sucessfully'));
	}
	
// 帖子详情 | post detail
} else {
	
	// thread-{tid}-{page}-{keyword}.htm
	$tid = param(1, 0);
	$page = param(2, 1);
	$keyword = param(3);
	$pagesize = $conf['postlist_pagesize'];
	//$pagesize = 10;
	//$page == 1 AND $pagesize++;
	
	
	
	$thread = thread_read($tid);
	empty($thread) AND message(-1, lang('thread_not_exists'));
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(3, lang('forum_not_exists'));
	
	$postlist = post_find_by_tid($tid, $page, $pagesize);
	empty($postlist) AND message(4, lang('post_not_exists'));
	
	if($page == 1) {
		empty($postlist[$thread['firstpid']]) AND message(-1, lang('data_malformation'));
		$first = $postlist[$thread['firstpid']];
		unset($postlist[$thread['firstpid']]);
		$attachlist = $imagelist = $filelist = array();
		
		// 如果是大站，可以用单独的点击服务，减少 db 压力
		// if request is huge, separate it from mysql server
		thread_inc_views($tid);
	} else {
		$first = post_read($thread['firstpid']);
	}
	
	$keywordurl = '';
	if($keyword) {
		$thread['subject'] = post_highlight_keyword($thread['subject'], $keyword);
		//$first['message'] = post_highlight_keyword($first['subject']);
		$keywordurl = "-$keyword";
	}
	$allowpost = forum_access_user($fid, $gid, 'allowpost') ? 1 : 0;
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate') ? 1 : 0;
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete') ? 1 : 0;
	
	forum_access_user($fid, $gid, 'allowread') OR message(-1, lang('user_group_insufficient_privilege'));
	
	$pagination = pagination(url("thread-$tid-{page}$keywordurl"), $thread['posts'] + 1, $page, $pagesize);
	
	$header['title'] = $thread['subject'].'-'.$forum['name'].'-'.$conf['sitename']; 
	//$header['mobile_title'] = lang('thread_detail');
	$header['mobile_title'] = $forum['name'];;
	$header['mobile_link'] = url("forum-$fid");
	$header['keywords'] = ''; 
	$header['description'] = $thread['subject'];
	$_SESSION['fid'] = $fid;
	
	
	
	
$haya_post_like_config = setting_get('haya_post_like');

if (isset($haya_post_like_config['open_post'])
	&& $haya_post_like_config['open_post'] == 1
) {
	$hot_like_post_size = intval($haya_post_like_config['hot_like_post_size']);
	$hot_like_post_low_count = intval($haya_post_like_config['hot_like_post_low_count']);
	$haya_post_like_hot_posts = haya_post_like_find_hot_loves_by_tid($thread['tid'], $hot_like_post_size, $hot_like_post_low_count);
	
	$haya_post_like_post_ids = array();
	if (!empty($postlist)) {
		foreach ($postlist as $haya_post_like_post) {
			$haya_post_like_post_ids[] = $haya_post_like_post['pid'];
		}
	}
	
	if (!empty($haya_post_like_hot_posts)) {
		foreach ($haya_post_like_hot_posts as $haya_post_like_hot_post) {
			$haya_post_like_post_ids[] = $haya_post_like_hot_post['pid'];
		}
	}
	
	$haya_post_like_pids = haya_post_like_find_by_pids($haya_post_like_post_ids);
}


	
	include _include(APP_PATH.'view/htm/thread.htm');
}



?>
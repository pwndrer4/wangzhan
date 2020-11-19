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
 

 
 
  
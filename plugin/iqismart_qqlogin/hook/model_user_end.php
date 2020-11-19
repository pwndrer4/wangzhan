
function is_qq_bond($uid){
    $r = db_find_one('iqismart_qqlogin', array('uid'=>$uid));
    if (!$r){
        return false;
    }else{
        return true;
    }
}
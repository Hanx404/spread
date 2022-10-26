<?php
//公共方法类

//同步到crm短信
function async_crm_msg($out_trade_no,$is_pay=1){
    $sql_helper = new Mysql();
    $info = $sql_helper->get_list_bysql("select id,paytype,`type`,`name`,`mobile` from sys_spread_infor where trade_no='$out_trade_no'");
    $info = $info[0];
//    $count = $sql_helper->get_one_bysql('select count(id) from sys_spread_infor where paytype > 0 and type = '.$info['type'].' and mobile = '.$info['mobile']);
    sys_close_db($sql_helper);

    $timestamp = time();
    $post_content = [
        'id'=>$info['type'],
        'mobile'=>$info['mobile'],
        'name'=>$info['name']
    ];
    $post_data = [
        'content'=>json_encode($post_content),
        'timestamp'=>$timestamp,
        'is_pay'=>$is_pay,
        'sign'=>base64_encode(hash_hmac('sha256', $post_content['id'].'|'.$post_content['mobile'].'|'.$timestamp, 'mojinshi'))
    ];
    $crm_url = SYS_CRM_URL.'/webservice/notify/spread.html';
    $result = curl_post($crm_url,$post_data);
    return $result;

}
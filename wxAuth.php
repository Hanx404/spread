<?php

$code = $_GET['code'];//微信携带的
if(!$code){
    $id = $_GET['id'];
    //跳转去授权
    $redirectUri = urlencode("http://www.mojinshi.online/spread/wxAuth.php");
    $oauthUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc8ddae20da15b5b6&redirect_uri='.$redirectUri.'&response_type=code&scope=snsapi_userinfo&state='.$id.'#wechat_redirect';
    Header("Location:".$oauthUrl);
} else {

    //$state =
    //根据code获取信息
    $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxc8ddae20da15b5b6&secret=d36a09138ae01f7ba568e1a7b234462a&code=' . $code . '&grant_type=authorization_code';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $res = curl_exec($ch);
    curl_close($ch);
    $json_obj = json_decode($res, true);
    var_dump($json_obj);die;
    if(key_exists("errcode",$json_obj) && $json_obj['errcode']!=0) {
        echo "网络错误，请重新打开连接";exit;
    } else {
        $openid = $json_obj['openid'];
        $access_token =  $json_obj['access_token'];
        $session_key = $json_obj['session_key'];


        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $res = curl_exec($ch);
        curl_close($ch);
        $resArr = json_decode($res, true);
        var_dump($resArr);
        die;


    }
}

<?php
/**
* 	配置账号信息
*/
class WxPayConf_pub
{

    //define("WX_APP_ID","wxbdce4ab9e58d8e6b");
    //define("WX_APP_SECRET","19e29bcf485c872aa5033c33fd902681");
    //define("WX_MCH_ID","1493535482");
    //define("WX_API_KEY","shandongchukexiaochengxukaifa123");
	//=======【基本信息设置】=====================================
	//微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
	const APPID = "wxc8ddae20da15b5b6";
	//受理商ID，身份标识
	const MCHID = "1585204531";
	//商户支付密钥Key。审核通过后，在微信发送的邮件中查看
	const KEY = "shandongchukexiaochengxukaifa123";
	//JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
	const APPSECRET = "shandongchukexiaochengxukaifa123";
	
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'http://www.xxx.com/plugins/OnlinePay/WeixinWeb/trade.php';
	
	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = '/home/www/activity/plugins/OnlinePay/Weixinpay/WxPayPubHelper/cacert/apiclient2_cert.pem';
	const SSLKEY_PATH = '/home/www/activity/plugins/OnlinePay/Weixinpay/WxPayPubHelper/cacert/apiclient2_key.pem';
	
	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
	const NOTIFY_URL = SYS_PLUGINS.'OnlinePay/Weixinpay/notify_url.php';

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}
	
?>
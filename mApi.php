<?php
/**
 * 推广页面API
 *
 */


require_once '../include/system.core.php';
require_once 'common/common.php';
//关闭
error_reporting( E_ERROR);

//每期的价格
global $price_map;
$price_map = [
    '1'=>0.01,
    '2'=>0.01,
];

$a = _GET('a');

if(in_array($a,['api_get_code','api_infor_submit','api_status_get','juliang_callback','get_person_list','apply_infor_submit'])){
    $a();
} else {
    sys_out_fail('无效访问');
}

/**
 * 获取支付状态
 */

function api_status_get()
{
    $no = _POST('no');
    if(sys_check_empty($no)) sys_out_fail();
    $sql_helper = new Mysql();
    $paytype = $sql_helper->get_one_bysql("select paytype from sys_spread_infor where trade_no='$no'");
    sys_close_db($sql_helper);
    if($paytype>0) sys_out_success();
    sys_out_fail();
}

/**
 * 获取手机验证码
 */
function api_get_code(){

    $mobile = _POST('mobile');

    if(sys_check_empty($mobile)) sys_out_fail('请填写手机号');

    $content=rand(1000,9999);
    require_once "../plugins/aliyun_dysms/api_demo/SmsDemo.php";//此处加载只为方便/plugins第三方插件使用
    $response = SmsDemo::sendSms(
        "磨金石", // 短信签名
        "SMS_172351780", // 短信模板编号
        $mobile, // 短信接收者
        Array(  // 短信模板中字段的值
            "code"=>$content
        )
    );

    $response_str = json_encode($response,JSON_UNESCAPED_UNICODE);
//    var_dump($response);
    if($response->Code!='OK') sys_out_fail("发送失败，稍后请重试",600);//网络故障

    $_SESSION['s_mobile'] = $mobile;
    $_SESSION['s_code'] = $content;

    sys_out_success();


}

/**
 * 提交信息，返回支付链接
 *
 */
function api_infor_submit(){
    $mobile = _POST('mobile');
    $name = _POST('name');
    $code = _POST('code');
    $paytype = _POST('paytype');
    $wxcode = _POST('wxcode');//微信授权的code，微信浏览器内访问时 支付需要用openid
    $type = _POST('type'); //推广期数

    if(sys_check_empty($name)) sys_out_fail('请填写姓名');
    if(sys_check_empty($mobile)) sys_out_fail('请填写手机号');
    if(sys_check_empty($code)) sys_out_fail('请填写验证码');

    if(_SESSION('s_code')!=$code) sys_out_fail("验证码错误");
    if(_SESSION('s_mobile')!=$mobile) sys_out_fail("手机号不一致");

    $trade_no = "MJS".date("YmdHis").sys_create_code();
    global $price_map;
    $price = $price_map[$type];

    $sql_helper = new Mysql();
    $res = $sql_helper->do_execute("insert into sys_spread_infor set type='$type',trade_no='$trade_no',price=$price,name='$name',mobile='$mobile',addtime='".sys_get_time()."'");

    sys_close_db($sql_helper);
    if(!$res) sys_out_fail("提交失败，请重试");
    //推送crm消息
    $ret_msg = async_crm_msg($trade_no,0);
    //支付链接
    if($paytype == 1)//微信
    {
        include_once("./Weixinpay/WxPayPubHelper/WxPayPubHelper.php");
        $unifiedOrder = new UnifiedOrder_pub();
        $unifiedOrder->setParameter("body","磨金石教育");//商品描述

        $unifiedOrder->setParameter("out_trade_no","$trade_no");//商户订单号
        $unifiedOrder->setParameter("total_fee",$price*100);//总金额
        $unifiedOrder->setParameter("notify_url",SYS_ROOT."spread/Weixinpay/notify_url.php");//通知地址
        //判断是否在微信浏览器访问
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            //非微信的用h5支付
            $unifiedOrder->setParameter("trade_type","MWEB");//交易类型

            $unifiedOrderResult = $unifiedOrder->getResult();
            // var_dump($unifiedOrderResult);die;
            if ($unifiedOrderResult["return_code"] == "FAIL")
            {
                //商户自行增加处理流程
                //sys_out_fail("出错：".$unifiedOrderResult['return_msg']);
                sys_out_fail("网络错误");
            }
            elseif($unifiedOrderResult["result_code"] == "FAIL")
            {
                sys_out_fail("网络错误");
                //商户自行增加处理流程
                sys_out_fail("错误代码：".$unifiedOrderResult['err_code']);
                sys_out_fail("错误代码描述：".$unifiedOrderResult['err_code_des']);
            }
            elseif($unifiedOrderResult["mweb_url"] != NULL)
            {
                $url = $unifiedOrderResult["mweb_url"];
                $url = $url."&redirect_url=".urlencode("http://www.mojinshi.online/spread/wxpayReturn.html?no={$trade_no}&type={$type}");
                //  header("Location:".$url);
                sys_out_success(0,$url);

            }
        } else {
           // echo "是微信";
            if(sys_check_empty($wxcode)) sys_out_fail('请先微信授权');
            //微信浏览器内部用JSAPI支付
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxc8ddae20da15b5b6&secret=d36a09138ae01f7ba568e1a7b234462a&code=' . $wxcode . '&grant_type=authorization_code';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $res = curl_exec($ch);
            curl_close($ch);
            $json_obj = json_decode($res, true);
            if(key_exists("errcode",$json_obj) && $json_obj['errcode']!=0) {
                echo "网络错误，请重新打开连接";exit;
            } else {
                $openid = $json_obj['openid'];
                $unifiedOrder->setParameter("openid","$openid");
                $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
                $prepay_id = $unifiedOrder->getPrepayId();

                //=========步骤3：使用jsapi调起支付============
                $jsApi = new JsApi_pub();
                $jsApi->setPrepayId($prepay_id);
                $jsApiParameters = $jsApi->getParameters();
                $arr = json_decode($jsApiParameters,true);
                $arr['no']=$trade_no;
                $temp_array = array();
                $temp_array[0] = $arr;
                sys_out_success(0,$arr);
            }
        }

    }
    else if($paytype == 2)//支付宝
    {
        require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'alipaywappay/wappay/service/AlipayTradeService.php';
        require_once dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'alipaywappay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        require dirname ( __FILE__ ).DIRECTORY_SEPARATOR.'alipaywappay/config.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $trade_no;

        //订单名称，必填
        $subject = "磨金石教育";
        //$price = 0.01;
        //付款金额，必填
        $total_amount = $price;

        //商品描述，可空
        $body = '磨金石教育';

        //超时时间
        $timeout_express="1m";

        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);

        $payResponse = new AlipayTradeService($config);
        $result=$payResponse->wapPay($payRequestBuilder,"https://www.mojinshi.online/spread/paymentSuccess.html?type={$type}",$config['notify_url']);

        sys_out_success(0,$result);
        //return ;

    }

    //sys_out_success();



}

//测试接口
function test(){
    include_once 'common/common.php';

   var_dump( async_crm_msg('MJS202201131732176275'));
}

//巨量广告回传
function juliang_callback(){
    $clickid = _POST('clickid');
    if($clickid){
        $url = 'https://analytics.oceanengine.com/api/v2/conversion';
        $data = [
            'event_type'=>'form',
            'context'=>[
                'ad'=>[
                    'callback'=>$clickid
                ]
            ],
            'timestamp'=>time()
        ];

        $data = json_encode($data);
        $header= ['Content-Type: application/json'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);	//连接服务器等待超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);	//缓冲完成超时时间

        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);

        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result=curl_exec($ch);
        curl_close($ch);

        $result_arr = json_decode($result,true);

        sys_out_success('',$result_arr);
    }

}

//返回随机数据
function get_person_list(){
    $data = [
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/105.jpg',
            'name'=>'小**'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/22.jpg',
            'name'=>'华***'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/15.jpg',
            'name'=>'赵**'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/101.jpg',
            'name'=>'奥***'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/108.jpg',
            'name'=>'唐*'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/19.jpg',
            'name'=>'风**'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/22.jpg',
            'name'=>'安*'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/29.jpg',
            'name'=>'昊***'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/55.jpg',
            'name'=>'ya***'
        ],
        [
            'avatar'=>'https://www.mojinshi.online/uploadfiles/default_avatar/87.jpg',
            'name'=>'路***'
        ],
    ];
    sys_out_success('',$data);
}

//活动广告报名
function apply_infor_submit(){
    $mobile = _POST('mobile');
    $name = _POST('name');
    $id = _POST('id');

    if(sys_check_empty($name)) sys_out_fail('请填写姓名');
    if(sys_check_empty($mobile)) sys_out_fail('请填写手机号');

    //验证手机号格式
    $chars = "/^((\(\d{2,3}\))|(\d{3}\-))?1(2|3|4|5|6|7|8|9)\d{9}$/";
    if (!preg_match($chars, $mobile)){
        sys_out_fail('请输入正确的手机号');
    }

    //判断是否报过名没
    $sql_helper = new Mysql();
    //判断该活动是否存在
    $apply =  $sql_helper->get_one_bysql("select * from sys_apply where id = {$id}");
    if(!$apply){
        sys_out_fail('该活动不存在');
    }
    $info = $sql_helper->get_one_bysql("select * from sys_apply_list where mobile = {$mobile} and apply_id = {$id}");
    if($info){
        sys_out_fail('您已报名该活动');
    }
    $nowtime = time();
    $res = $sql_helper->do_execute("insert into sys_apply_list (apply_id,name,mobile,createtime) values({$id},'{$name}','{$mobile}',$nowtime)");
    sys_close_db($sql_helper);
    if(!$res){
        sys_out_fail("提交失败，请重试");
    }
    sys_out_success('报名成功');

}

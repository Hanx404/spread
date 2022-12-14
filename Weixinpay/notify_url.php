<?php
/**
 * 通用通知接口demo
 * ====================================================
 * 支付完成后，微信会把相关支付和用户信息发送到商户设定的通知URL，
 * 商户接收回调信息后，根据需要设定相应的处理流程。
 * 
 * 这里举例使用log文件形式记录回调信息。
*/
//嵌入系统核心配置文件config.inc.php
	require_once("../../include/system.core.php");
	include_once("./log_.php");
	include_once("WxPayPubHelper/WxPayPubHelper.php");
	include_once('../common/common.php');

	//var_dump(11);die;
    //使用通用通知接口
	$notify = new Notify_pub();

	//存储微信的回调
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];	
	$notify->saveData($xml);
	
	//验证签名，并回应微信。
	//对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
	//微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
	//尽可能提高通知的成功率，但微信不保证通知最终能成功。
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;
	
	//==商户根据实际情况设置相应的处理流程，此处仅作举例=======
	
	//以log文件形式记录回调信息
	$log_ = new Log_();
	$log_name="./notify_url.log";//log文件路径
	$log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

	//ext_pay_notify($notify);

	if($notify->checkSign() == TRUE)
	{
		if ($notify->data["return_code"] == "FAIL") {
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
		}
		elseif($notify->data["result_code"] == "FAIL"){
			//此处应该更新一下订单状态，商户自行增删操作
			$log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
		}
		else{
			//此处应该更新一下订单状态，商户自行增删操作
			$out_trade_no = $notify->data["out_trade_no"];// 我方订单号
            $trade_no = $notify->data["transaction_id"];// 微信支付订单号
            //$total_fee = $notify->data["total_fee"]/100;// 交易金额，转换为元
            //调用支付成功业务处理函数（在include\extend.inc.php中定义）
            $sql_helper = new Mysql();
            $info = $sql_helper->get_list_bysql("select id,paytype from sys_spread_infor where trade_no='$out_trade_no'");
            if($info[0]['paytype']==0){
                $sql_helper->do_execute("update sys_spread_infor set paytype=1,out_trade_no='$trade_no' where id=".$info[0]['id']);
            }
            sys_close_db($sql_helper);

            //同步短信
            async_crm_msg($out_trade_no);


            $log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
		}
		
		//商户自行增加处理流程,
		//例如：更新订单状态
		//例如：数据库操作
		//例如：推送支付完成信息
	}
?>
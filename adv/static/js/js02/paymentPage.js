var pay_type; //支付状态
//判断当前浏览器是不是微信浏览器
function isWeiXin() {
    var ua = window.navigator.userAgent.toLowerCase();

    if (ua.match(/MicroMessenger/i) == 'micromessenger') {
        $('#weixinPay').hide();
        $('#aliPay').hide();
        pay_type = 1;
        return true;
    } else {
        return false;
    }
}

pay_type = 2; //进入页面默认选择支付宝

// 判断是否是微信内部浏览器打开
function getUrlParam(data) {
    let reg = new RegExp("(^|&)" + data + "=([^&]*)(&|$)");
    let r = window.location.search.substr(1).match(reg);
    if (r != null)
        return decodeURI(r[2]);
    return null;
}

if (isWeiXin()) {
    let url = window.location.href;
    //判断有没有code
    let code = getUrlParam('code');
    if (!code) {
        url = encodeURIComponent(url);
        window.location.href = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc8ddae20da15b5b6&redirect_uri=' + url + '&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
    }

} else {
    console.log("不是来自微信内置浏览器");
}


// 随时间增加人数增加
var aaa = 2711;
$(function () {
    function initial(num, initial, time) {
        var time = time;
        var oldnum = 3600 * time / 1000;
        var o = $('.personNum')
        var start_Date = new Date(initial);
        var sec = (new Date().getTime() - start_Date.getTime()) / oldnum;
        var nowNum = parseInt(sec + num);
        setInterval(function () {
            ++nowNum;
        }, time)
        o.html(nowNum);
    };

    initial(aaa, '2022/1/17 10:31', 600000);

})


var isClick = true; //防止连续点按，间隔5000毫秒
$('#vCode').click(function () {
    if (isClick) {
        isClick = false;
        let phoneNum = $('#inputTwo').val();
        let VCode = $('#inputThree').val();

        let verificationPhone = /^1\d{10}$/;
        verPhone = verificationPhone.test(phoneNum);
        if (!verPhone) {
            alert('请输入11位正确手机号');
            return;
        }

        // 验证码倒计时
        var second = 60;
        var time = setInterval(function () {
            if (second > 0) {
                $('#vCode').text(second + 's后重试')
                second--;
                $('#vCode').css('background', '#ccc');
                $('#vCode').css({
                    "pointer-events": "none"
                })
            } else {
                $('#vCode').text('获取验证码')
                $('#vCode').css('background', '#9d76f7');
                clearInterval(time);
            }
        }, 1000);

        let url = '/spread/mApi.php?a=api_get_code';
        // let url = 'http://mojinshi.cc/spread/mApi.php?a=api_get_code';
        let data = {
            mobile: phoneNum
        }
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success(res) {
                res = JSON.parse(res);
                let res_msg = res.msg;
                if (res.success) {} else {
                    alert(res_msg);
                }
            }
        })
    }
    setTimeout(function () {
        isClick = true;
    }, 5000)


})

//调取支付接口
function pay(paytype, wxcode, func) {
    let fullName = $('#inputOne').val();
    let phoneNum = $('#inputTwo').val();
    let VCode = $('#inputThree').val();

    let url = '/spread/mApi.php?a=api_infor_submit';
    let data = {
        mobile: phoneNum,
        name: fullName,
        code: VCode,
        paytype: paytype,
        wxcode: wxcode,
        type: 2
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success: func
    });
}


// 选择微信
$('#weixinPay').click(function () {
    $('.weixinPayImgThree').show();
    $('.weixinPayImgTwo').hide();
    $('.aliPayImgTwo').hide();
    $('.aliPayImgThree').show();
    pay_type = 1; //微信支付传1
})

// 选择支付宝
$('#aliPay').click(function () {
    $('.aliPayImgTwo').show();
    $('.aliPayImgThree').hide();
    $('.weixinPayImgTwo').show();
    $('.weixinPayImgThree').hide();
    pay_type = 2; //支付宝支付传2
})

// 点击确认付款
$('#confirmPayBtn').click(function () {
    if (isClick) {
        isClick = false;
        if (pay_type == 2) {
            pay(pay_type, '', function (ress) {
                ress = JSON.parse(ress);
                let ress_msg = ress.msg;
                if (ress.success) {
                    $('#aliPayPage').append(ress.infor);
                } else {
                    alert(ress_msg);
                    return;
                }

            });
        } else if (pay_type == 1) {
            let wxcode = '';
            let isWx = isWeiXin();
            if (isWx) {
                //判断有没有code
                wxcode = getUrlParam('code');
            }
            pay(pay_type, wxcode, function (res) {
                res = JSON.parse(res);
                let res_msg = res.msg;
                if (res.success) {
                    if (isWx) {
                        WeixinJSBridge.invoke(
                            'getBrandWCPayRequest', {
                                "appId": res.infor.appId, //公众号名称，由商户传入
                                "timeStamp": res.infor.timeStamp, //时间戳，自1970年以来的秒数
                                "nonceStr": res.infor.nonceStr, //随机串
                                "package": res.infor.package,
                                "signType": res.infor.signType, //微信签名方式：
                                "paySign": res.infor.paySign //微信签名
                            },
                            function (res) {
                                if (res.err_msg == "get_brand_wcpay_request:ok") {
                                    // 使用以上方式判断前端返回,
                                    // 微信团队郑重提示：
                                    // res.err_msg将在用户支付成功后返回ok， 但并不保证它绝对可靠。
                                    window.location.href = '/spread/paymentSuccess.html?type=' + 2;
                                }
                            }
                        );
                    } else {
                        window.location.href = res.infor;
                    }

                } else {
                    alert(res_msg);
                    return;
                }
            });
        }
    }
    setTimeout(function () {
        isClick = true
    }, 5000)
})
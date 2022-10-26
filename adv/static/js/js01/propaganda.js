// $(function () {
//     //平台、设备和操作系统 
//     var system = {
//         win: false,
//         mac: false,
//         xll: false,
//         ipad: false
//     };
//     //检测平台 
//     var p = navigator.platform;
//     system.win = p.indexOf("Win") == 0;
//     system.mac = p.indexOf("Mac") == 0;
//     system.x11 = (p == "X11") || (p.indexOf("Linux") == 0);
//     system.ipad = (navigator.userAgent.match(/iPad/i) != null) ? true : false;
//     //跳转语句，如果是手机访问就自动跳转到wap.baidu.com页面 
//     if (system.win || system.mac || system.xll || system.ipad) {
//         alert("在PC端上打开的");
//         localStorage.setItem('ceshi','000')
//     } else {
//         var ua = navigator.userAgent.toLowerCase();
//         if (ua.match(/MicroMessenger/i) == "micromessenger") {
//             alert("在手机端微信上打开的");
//         } else {
//             alert("在手机上非微信上打开的");
//         }
//     }
// })

// 上面的限时秒杀
$("#seckillTop").click(function () {
    location.href = 'https://www.mojinshi.online/spread/1/paymentPage.html';
})

// 底部的限时秒杀
$("#seckillBottom").click(function () {
    location.href = 'https://www.mojinshi.online/spread/1/paymentPage.html';
})
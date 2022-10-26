// 上面的限时秒杀
$("#seckillTop").click(function () {
    window.location.href = '/spread/adv/1/paymentPage.html';
})

// 底部的限时秒杀
$("#seckillBottom").click(function () {
    window.location.href = '/spread/adv/1/paymentPage.html';
})

// 点击马上抢购
// $(".buy").click(function () {
//     window.location.href = '/spread/adv/1/paymentPage.html';
// })
$(document).on('click', '.buy', function () {
    window.location.href = '/spread/adv/1/paymentPage.html';
})

var aaa = 2596;
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

    initial(aaa, '2022/1/15 11:04', 600000);
    personsNum();
})

// 倒计时
var intDiff = parseInt(60); //倒计时总秒数量
function timer() {
    window.setInterval(function () {
        for (let i = 0; i < 10; i++) {
            let temp_timer = $('.timer-' + i).children('.s');
            let time = $(temp_timer).html();
            time = parseInt(time) - 1;
            if (time < 0) {
                time = parseInt(5.5 + 8 * 3.4);
            }
            $(temp_timer).html(time)
        }
    }, 1000);
}

function timer2() {
    window.setInterval(function () {
        for (let i = 0; i < 10; i++) {
            let temp_timer = $('.timer-' + i).children('.hs');
            let time = $(temp_timer).html();
            time = parseInt(time) - 1
            if (time < 0) {
                time = 9;
            }
            $(temp_timer).html(time)
        }
    }, 100);
}

function add0(m) {
    return m < 10 ? '0' + m : m
}


function personsNum() {
    let url = '/spread/mApi.php?a=get_person_list';
    $.ajax({
        type: 'GET',
        url: url,
        success(res) {
            res = JSON.parse(res)
            if (res.success) {
                let resAll = res.infor;
                resAll.forEach((item, index) => {
                    var dayTime = new Date().getTime();
                    let actionTime = parseInt(5.5 + index * 3.3);

                    str = `
                   
                    <div class="swiper-slide">
                        <div class="firstBox">
                        <div class="avatarOne">
                            <img src="${item.avatar}" alt="">
                        </div>
                        <span class="nameOne">${item.name}</span>
                        <div class="timeLimit">
                                <span class="timeLimitOne">原价:&nbsp;199</span>
                                秒杀价:
                                <span class="timeLimitTwo">0.99</span><br />
                                <span class="timeLimitThree">剩余</span>
                                <span class="timer-${index}">
                                    00:00:0<span class="s">${actionTime}</span>.<span class="hs">9</span>
                                </span>
                            </div>
                            <div class="buy">马上抢</div>
                        </div>
                    </div>
                    `;
                    $(".swiper-wrapper").append(str);
                });

                // 轮播图
                var mySwiper = new Swiper('.swiper', {
                    direction: 'vertical', // 垂直切换选项
                    autoHeight: true,
                    observer: true,
                    observeParents: true,
                    initialSlide: 0,
                    slidesPerView: 'auto',
                    loop: true, // 循环模式选项
                    autoplay: true, //可选选项，自动滑动
                })

                timer();
                timer2();
            }
        }
    })
}
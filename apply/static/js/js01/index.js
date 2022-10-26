function sign() {
    let fullName = $('#nameForm').val();
    let phoneNum = $('#phoneForm').val();
    let url = '/spread/mApi.php?a=apply_infor_submit';
    let data = {
        name: fullName,
        mobile: phoneNum,
        id: 1
    }
    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success(res) {
            res = JSON.parse(res)
            console.log(res, 'res123')
            if (res.success) {
                alert(res.msg)
            } else {
                alert(res.msg)
            }
        }
    })
}

var isClick = true;
$("#signUp").click(function () {
    if (isClick) {
        isClick = false;
        sign();
    }

    setTimeout(function () {
        isClick = true
    }, 10000)
})
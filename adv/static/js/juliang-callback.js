//获取query参数
function getUrlParam(data){
    let reg = new RegExp("(^|&)"+ data +"=([^&]*)(&|$)");
    let r = window.location.search.substr(1).match(reg);
    if(r!=null)
        return  decodeURI(r[2]);
    return null;
}

//回调巨量广告平台
function juliang_callback(){
    let clickid = getUrlParam('clickid')
    if(clickid){
        let url = '/spread/mApi.php?a=juliang_callback'
        let data = {clickid:clickid}
        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(res){
                // alert(res)
            }
        });

    }
    console.log('回调完成')
}

//运行
juliang_callback()
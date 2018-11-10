function developing_tips(){
    alert('正在建设中...');
    return false;
}


function loading(){
    $('.loading').show();
}
function stopLoading(){
    $('.loading').hide();
}

function mylog(data){
    console.log(data);
}
function successBase(res){
      if( res.code == 0 ){
          layer.msg(res.msg);
      }else{
          console.log(res);
          layer.msg(res.msg);
      }
  }

  function successReload(res){
    if( res.code == 0 ){
      reload();
      layer.msg(res.msg,{icon: 1,time:1000});
    }else{
      layer.msg(res.msg,{icon: 2,time:2000});
    }
  }

  function successDelete(res){
    if( res.code == 0 ){
      layer.msg(res.msg,{icon: 1,time:1000});
    }else{
      layer.msg(res.msg,{icon: 2,time:2000});
    }
  }
  function successLayui(res){
      if( res.code == 0 ){
            layer.msg(res.msg,{icon: 1,time:1000});
            parent.reload();
            // 获得frame索引
            var index = parent.layer.getFrameIndex(window.name);
              //关闭当前frame
            parent.layer.close(index);
          
      }else{
          layer.msg(res.msg,{icon: 2,time:2000});
      }
  }
  function reload() {
    window.location.reload();
  }

function jqueryAjax(type,url,postData,success,dataType='json'){
    $.ajax({
        type:type,
        url:url,
        data:postData,
        dataType:dataType,
        success:function(res){
            success(res);
        },
        error:function(res){
            alert('系统繁忙，请重试！');
            return false;
        }
    });
}
/*弹出层*/
/*
    参数解释：
    title   标题
    url     请求的url
    id      需要操作的数据id
    w       弹出层宽度（缺省调默认值）
    h       弹出层高度（缺省调默认值）
*/
function x_admin_show(title,url,w,h){
    if (title == null || title == '') {
        title=false;
    };
    if (url == null || url == '') {
        url="404.html";
    };
    if (w == null || w == '') {
        w=($(window).width());
    };
    if (h == null || h == '') {
        h=($(window).height());
    };
    layer.open({
        type: 2,
        area: [w+'px', h +'px'],
        fix: false, //不固定
        maxmin: true,
        shadeClose: true,
        shade:0.4,
        title: title,
        content: url,
    });
}
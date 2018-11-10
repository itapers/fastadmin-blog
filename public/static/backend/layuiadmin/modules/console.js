/**

 @Name：layuiAdmin 主页控制台
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License：GPL-2
    
 */


layui.define(function(exports){
  
  /*
    下面通过 layui.use 分段加载不同的模块，实现不同区域的同时渲染，从而保证视图的快速呈现
  */
  
  
  //区块轮播切换
  layui.use(['admin', 'carousel'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,carousel = layui.carousel
    ,element = layui.element
    ,device = layui.device();

    //轮播切换
    $('.layadmin-carousel').each(function(){
      var othis = $(this);
      carousel.render({
        elem: this
        ,width: '100%'
        ,arrow: 'none'
        ,interval: othis.data('interval')
        ,autoplay: othis.data('autoplay') === true
        ,trigger: (device.ios || device.android) ? 'click' : 'hover'
        ,anim: othis.data('anim')
      });
    });
    element.render('progress');
  });


  //最新订单
  layui.use('table', function(){
    var $ = layui.$
    ,table = layui.table;
    
    //今日热搜
    table.render({
      elem: '#LAY-index-topSearch'
      ,url: "/backend/Customer/getData" //数据接口
     // ,page: true
      ,cols: [[
         {field: 'id',title: '编号', width: 80, sort: true}
        ,{field: 'name', title: '客户名称',  sort: true}
        ,{field: 'mobile', title: '手机号', sort: true}
        ,{field: 'cate_name', title: '客户等级', sort: true}
        ,{field: 'ctime', title: '创建时间', sort: true}
      ]]
      ,skin: 'line'
    });
    
    
  });
  
  exports('console', {})
});
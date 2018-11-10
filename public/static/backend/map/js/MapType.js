/**
 * [tMapKey 秘钥]
 * @type {Object}
 */

var tMapKey = {
	baidu : 'v22uaSd5zGqvQbBIAvnAM89LjcgYcdkd',
	gould : 'c193b7c51e579ad80ec35989db0d8898'
}
//百度
var baiduMap ={
	show : function(){
		baiduMap.loScript();
	},
	loScript : function(){
		var script = document.createElement("script");  
		script.src = "https://api.map.baidu.com/api?v=2.0&ak="+tMapKey.baidu+"&callback=initialize";
		document.getElementsByTagName('head')[0].appendChild(script);  
	},
	createMap : function(obj){

		var map = new BMap.Map(obj);          // 创建地图实例  
		var point = new BMap.Point(116.404, 39.915);  // 创建点坐标  
		map.centerAndZoom(point, 15);                 // 初始化地图，设置中心点坐标和地图级别 
		map.enableScrollWheelZoom(true);
		var geolocation = new BMap.Geolocation();
		geolocation.getCurrentPosition(function(r){
			if(this.getStatus() == BMAP_STATUS_SUCCESS){
				var mk = new BMap.Marker(r.point);
				map.addOverlay(mk);
				map.panTo(r.point);
				var str=['定位成功'];
		        str.push('<div class="map_longitude" data-getLng="' + r.point.lng+'"> 经度：' + r.point.lng+'</div>');
		        str.push('<div class="map_latitude" data-getLat="' + r.point.lat+'">纬度：' + r.point.lat+'</div>'); 
		        document.getElementById('baiduTip').innerHTML = str.join(' ');
			}
			else {
				console.log('failed'+this.getStatus());
			}        
		},{enableHighAccuracy: true})
	//关于状态码
	//BMAP_STATUS_SUCCESS	检索成功。对应数值“0”。
	//BMAP_STATUS_CITY_LIST	城市列表。对应数值“1”。
	//BMAP_STATUS_UNKNOWN_LOCATION	位置结果未知。对应数值“2”。
	//BMAP_STATUS_UNKNOWN_ROUTE	导航结果未知。对应数值“3”。
	//BMAP_STATUS_INVALID_KEY	非法密钥。对应数值“4”。
	//BMAP_STATUS_INVALID_REQUEST	非法请求。对应数值“5”。
	//BMAP_STATUS_PERMISSION_DENIED	没有权限。对应数值“6”。(自 1.1 新增)
	//BMAP_STATUS_SERVICE_UNAVAILABLE	服务不可用。对应数值“7”。(自 1.1 新增)
	//BMAP_STATUS_TIMEOUT	超时。对应数值“8”。(自 1.1 新增)
		map.addEventListener('click',function(){
			var center = map.getCenter();
			document.querySelector('.editmap_map .map_longitude').innerHTML ='经度:'+center.lng;
			document.querySelector('.editmap_map .map_latitude').innerHTML ='经度:'+center.lat;
	    	document.querySelector('.editmap_map  .map_longitude').setAttribute("data-getLng",''+center.lng+'') ;
			document.querySelector('.editmap_map .map_latitude').setAttribute( "data-getLat",''+center.lat+'');

		});
		var ac = new BMap.Autocomplete({    //建立一个自动完成的对象
				"input" : "editmap_id",
				"location" : map
		});

		ac.addEventListener("onhighlight", function(e) {  //鼠标放在下拉列表上的事件
			var str = "";
			var _value = e.fromitem.value;
			var value = "";
			if (e.fromitem.index > -1) {
				value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
			}    
			str = "FromItem<br />index = " + e.fromitem.index + "<br />value = " + value;
			value = "";
			if (e.toitem.index > -1) {
				_value = e.toitem.value;
				value = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
			}    
			str += "<br />ToItem<br />index = " + e.toitem.index + "<br />value = " + value;
			document.getElementById("searchResultPanel").innerHTML = str;
		});

		var myValue;
		ac.addEventListener("onconfirm", function(e) {    //鼠标点击下拉列表后的事件
			var _value = e.item.value;
			myValue = _value.province +  _value.city +  _value.district +  _value.street +  _value.business;
			document.getElementById("searchResultPanel").innerHTML ="onconfirm<br />index = " + e.item.index + "<br />myValue = " + myValue;
			
			setPlace();
		});

		function setPlace(){
			map.clearOverlays();    //清除地图上所有覆盖物
			function myFun(){
				var pp = local.getResults().getPoi(0).point;    //获取第一个智能搜索的结果
				map.centerAndZoom(pp, 18);
				map.addOverlay(new BMap.Marker(pp));    //添加标注
			}
			var local = new BMap.LocalSearch(map, { //智能搜索
			  onSearchComplete: myFun
			});
			local.search(myValue);
		}
	}
};
//高德
var gouldMap = {
	show : function(){
		gouldMap.loScript();
	},
	loScript : function(){
		var script = document.createElement("script");  
		script.src = "http://webapi.amap.com/maps?v=1.4.0&key="+tMapKey.gould+"&plugin=AMap.Autocomplete";
		document.getElementsByTagName('head')[0].appendChild(script); 
	},
	createMap : function(obj){
		var map = new AMap.Map(obj,{
            resizeEnable: true,
            zoom: 10,
            center: [116.480983, 40.0958]
        });

        map.plugin('AMap.Geolocation', function() {

	        geolocation = new AMap.Geolocation({
	            enableHighAccuracy: true,//是否使用高精度定位，默认:true
	            timeout: 10000,          //超过10秒后停止定位，默认：无穷大
	            buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
	            zoomToAccuracy: true,      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
	            buttonPosition:'RB'
	        });
	        map.addControl(geolocation);
	        geolocation.getCurrentPosition();
	        console.log(onComplete)
	        AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
	        AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
	    });

	    //解析定位结果
	    function onComplete(data) {
	        var str=['定位成功'];
	        str.push('<div class="map_longitude" data-getLng="'+data.position.getLng()+'"> 经度：' + data.position.getLng()+'</div>');
	        str.push('<div class="map_latitude"  data-getLat="'+data.position.getLat()+'">纬度：' + data.position.getLat()+'</div>'); 
	        if(data.accuracy){ 
	             str.push('精度：' + data.accuracy + ' 米');
	        }//如为IP精确定位结果则没有精度信息
	        str.push('是否经过偏移：' + (data.isConverted ? '是' : '否'));
	        document.getElementById('tip').innerHTML = str.join(' ');
	    }

	    //解析定位错误信息
	    function onError(data) {
	        document.getElementById('tip').innerHTML = '定位失败';
	    }
      	map.plugin(['AMap.Autocomplete','AMap.PlaceSearch'],function(){
		     var autoOptions = {
		          city: "北京", //城市，默认全国
		          input: "editmap_id"//使用联想输入的input的id
		     };
		     autocomplete= new AMap.Autocomplete(autoOptions);
		     var placeSearch = new AMap.PlaceSearch({
		              city:'北京',
		              map:map
		     });
		     AMap.event.addListener(autocomplete, "select", function(e){
		           //TODO 针对选中的poi实现自己的功能
		           placeSearch.search(e.poi.name)
		     });
		});  
      	var _self = this;
		  //为地图注册click事件获取鼠标点击出的经纬度坐标
	    var clickEventListener = map.on('click', function(e) {
	    	document.querySelector('.map_longitude').innerHTML ='经度:'+e.lnglat.getLng();
	    	document.querySelector('.map_latitude').innerHTML ='纬度:'+e.lnglat.getLat();
	    	document.querySelector('.map_longitude').setAttribute( "data-getLng",''+e.lnglat.getLng()+'') ;
	    	document.querySelector('.map_latitude').setAttribute( "data-getLat",''+e.lnglat.getLat()+'') ;
	        // document.getElementById("lnglat").value = e.lnglat.getLng() + ',' + e.lnglat.getLat()
	       
	    });
	   var auto = new AMap.Autocomplete({
	        input: "tipinput"
	    });
	    AMap.event.addListener(auto, "select", select);//注册监听，当选中某条记录时会触发
	    function select(e) {
	        if (e.poi && e.poi.location) {
	            map.setZoom(15);
	            map.setCenter(e.poi.location);
	        }
	    }
	}
};

/**
		 * [returnMap 调用地图]
 * @param  {[type]} map [高德 百度]
 * @return {[type]}     [description]
 */
var returnMap = function(map){
	if(map.show instanceof  Function){
		 map.show();
	};
}

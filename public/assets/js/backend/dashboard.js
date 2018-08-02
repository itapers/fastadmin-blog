define(['jquery', 'bootstrap', 'backend', 'echartsobj'], function($, undefined, Backend, EchartObj) {

    var Controller = {
        index: function() {
            var options = {
                url: '',
                data: {
                    ctime: 3
                }
            }
            var chartOption = {
                targetId: 'echart',
                downLoadID: "#jyzs",
                downLoadTitle: '',
                type: 'line',
            }
            EchartObj.api.ajax(options, chartOption);
            // EchartObj.api.init({
            //     targetId:'echart',
            //     // mapType:'map/china',
            //     // map:{

            //     // }
            //     // bar:{
            //     //     xAxis:{
            //     //         data:["25~29", "30~34", "35~39", "40~44", "45~49", "50~54", "大于54"]
            //     //     },
            //     //     series:[{
            //     //         data:[{
            //     //             "name": "25~29",
            //     //             "number": "30001",
            //     //             "value": "15.64"
            //     //         }, {
            //     //             "name": "30~34",
            //     //             "number": "48358",
            //     //             "value": "25.21"
            //     //         }, {
            //     //             "name": "35~39",
            //     //             "number": "41893",
            //     //             "value": "21.84"
            //     //         }, {
            //     //             "name": "40~44",
            //     //             "number": "23092",
            //     //             "value": "12.04"
            //     //         }, {
            //     //             "name": "45~49",
            //     //             "number": "23315",
            //     //             "value": "12.15"
            //     //         }, {
            //     //             "name": "50~54",
            //     //             "number": "10653",
            //     //             "value": "5.55"
            //     //         }, {
            //     //             "name": "大于54",
            //     //             "number": "14471",
            //     //             "value": "7.54"
            //     //         }]
            //     //     }]
            //     // }
            //     // pie:{
            //     //     legend:{
            //     //         data:['男','女','未知']
            //     //     },
            //     //     series:[{
            //     //         name:'性别',
            //     //         data: [{
            //     //             "name": "男",
            //     //             "number": "68403",
            //     //             "value": "22.65"
            //     //         }, {
            //     //             "name": "女",
            //     //             "number": "112805",
            //     //             "value": "37.36"
            //     //         }, {
            //     //             "name": "未知",
            //     //             "number": "120672",
            //     //             "value": "39.97"
            //     //         }],
            //     //     }]
            //     // }
            //     line:{
            //         legend: {
            //             data: ['销售','订单']
            //         },
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //             name: '销售',
            //             type: 'line',
            //             smooth: true,
            //             areaStyle: {
            //                 normal: {
            //                 }
            //             },
            //             lineStyle: {
            //                 normal: {
            //                     width: 1.5
            //                 }
            //             },
            //             data: Orderdata.paydata
            //         },
            //         {
            //             name: '订单',
            //             type: 'line',
            //             smooth: true,
            //             areaStyle: {
            //                 normal: {
            //                 }
            //             },
            //             lineStyle: {
            //                 normal: {
            //                     width: 1.5
            //                 }
            //             },
            //             data: Orderdata.createdata
            //         }]
            //     }
            // })
            // var obj = EchartObj.api.line();
            // // var obj = EchartObj.api.line();
            // // 动态添加数据，可以通过Ajax获取数据然后填充
            // setInterval(function () {
            //     Orderdata.column.push((new Date()).toLocaleTimeString().replace(/^\D*/, ''));
            //     var amount = Math.floor(Math.random() * 200) + 20;
            //     Orderdata.createdata.push(amount);
            //     Orderdata.paydata.push(Math.floor(Math.random() * amount) + 1);

            //     //按自己需求可以取消这个限制
            //     if (Orderdata.column.length >= 20) {
            //         //移除最开始的一条数据
            //         Orderdata.column.shift();
            //         Orderdata.paydata.shift();
            //         Orderdata.createdata.shift();
            //     }
            //     obj.setOption({
            //         xAxis: {
            //             data: Orderdata.column
            //         },
            //         series: [{
            //                 name: '销售',
            //                 data: Orderdata.paydata
            //             },
            //             {
            //                 name: '订单',
            //                 data: Orderdata.createdata
            //             }]
            //     });
            // }, 2000);
        }
    };

    return Controller;
});
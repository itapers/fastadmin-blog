define(['echarts', 'echarts-theme', 'china', Config.store_enname], function(Echarts, undefined) {
    var EchartObj = {
        //默认配置，不被调用感染的配置
        config: {
            //使用主题
            theme: 'walden',
            //图表容器ID
            targetId: '',
            //下载容器ID,
            downLoadID: "",
            //下载图表标题,
            downLoadTitle: "",
            //图表可选颜色
            echarsColors: ['#F0D7B0', '#F47958', '#AA93F6', '#FFB062', '#FFD278', '#DC9FFF', '#CDB29F', '##F7C3C9', '#cab5e0', '#f28c63'],
            //后台返回数据格式
            /*
             *  单分类图表试用
             *  {
                    "column": ["男", "女", "未知"],
                    "columnData": [{
                        "name": "男",
                        "number": 3000,
                        "value": "30"
                    }, {
                        "name": "女",
                        "number": 6000,
                        "value": "60"
                    }, {
                        "name": "未知",
                        "number": 1000,
                        "value": "10"
                    }]
                }
            */
            /*
             *   多分类图表试用
             *   {
                     "column": ["男", "女", "未知"],
                     "xcolumnData": ["近1月", "近2月", "近3月", "近4月", "近5月", "近6月"],
                     "columnData": [{
                         "name": "男",
                         "type": "line",
                         "data": ["9", "100", "195", "130", "185", "172"]
                     }, {
                         "name": "女",
                         "type": "line",
                         "data": ["154", "144", "114", "174", "126", "192"]
                     }, {
                         "name": "未知",
                         "type": "line",
                         "data": ["54", "44", "14", "74", "26", "92"]
                     }]
                 }
             *
             */
            //柱形图配置项和数据
            bar: {
                color: '', //颜色
                tooltip: { //提示框组件。
                    trigger: 'axis', // 触发类型。可选项item:数据项图形触发，主要在散点图，饼图等无类目轴的图表中使用。axis:坐标轴触发，主要在柱状图，折线图等会使用类目轴的图表中使用。
                    axisPointer: { //坐标轴指示器配置项。
                        type: 'shadow' //指示器类型。可选项'line' 直线指示器。'shadow' 阴影指示器。'cross' 十字准星指示器。其实是种简写，表示启用两个正交的轴的 axisPointer。
                    },
                    formatter: function(param) { //格式化提示信息

                        // console.log(param);
                        return param[0].seriesName + ':' + param[0].name + '<br/>人数：' + param[0].data.number + '<br/> 占比：' + param[0].data.value + '%';

                    }
                },
                legend: { //图例配置
                    padding: 5,
                    bottom: '2%',
                    data: []
                },
                grid: { //直角坐标系内绘图网格
                    top: '15%', //grid 组件离容器上侧的距离。
                    left: '10%', //grid 组件离容器左侧的距离。
                    right: '10%', //grid 组件离容器右侧的距离。
                    bottom: '10%', //grid 组件离容器下侧的距离。
                    containLabel: true //grid 区域是否包含坐标轴的刻度标签。
                },
                xAxis: [ //直角坐标系 grid 中的 x 轴
                    {
                        type: 'category', //坐标轴类型。可选值：【'value' 数值轴，适用于连续数据。】【'category' 类目轴，适用于离散的类目数据，为该类型时必须通过 data 设置类目数据。】【'time' 时间轴，适用于连续的时序数据，与数值轴相比时间轴带有时间的格式化，在刻度计算上也有所不同，例如会根据跨度的范围来决定使用月，星期，日还是小时范围的刻度。】【'log' 对数轴。适用于对数数据。】
                        data: [], //类目数据，在类目轴（type: 'category'）中有效。
                        axisTick: { //坐标轴刻度相关设置。
                            alignWithLabel: true //类目轴中在 boundaryGap 为 true 的时候有效，可以保证刻度线和标签对齐。
                        },
                        axisLabel: { //坐标轴刻度标签的相关设置。
                            interval: 0, //坐标轴刻度标签的显示间隔，在类目轴中有效。【0 强制显示所有标签。】【1，表示『隔一个标签显示一个标签』】【2，表示隔两个标签显示一个标签】，以次类推
                            rotate: 45, //倾斜度 -90 至 90 默认为0
                            margin: 10, //刻度标签与轴线之间的距离。
                            textStyle: { //类目标签的文字样式。
                                color: '#797979', //文字的颜色。
                                fontStyle: 'normal' //文字的字体系列
                            }
                        },
                        axisLine: { //坐标轴轴线相关设置。
                            lineStyle: {
                                type: 'solid', //坐标轴线线的类型。
                                color: '#efefef', //坐标轴线线的颜色。
                                width: '2' //坐标轴线线宽。
                            }
                        },
                        show: true //是否显示 x 轴。
                    }
                ],
                yAxis: [ //直角坐标系 grid 中的 y 轴
                    {
                        type: 'value', //坐标轴类型。可选【'value' 数值轴，适用于连续数据。】【'category' 类目轴，适用于离散的类目数据，为该类型时必须通过 data 设置类目数据。】【'time' 时间轴，适用于连续的时序数据，与数值轴相比时间轴带有时间的格式化，在刻度计算上也有所不同，例如会根据跨度的范围来决定使用月，星期，日还是小时范围的刻度。】【'log' 对数轴。适用于对数数据。】
                        axisLabel: { //坐标轴刻度标签的相关设置。坐标轴刻度标签的显示间隔，在类目轴中有效。【0 强制显示所有标签。】【1，表示『隔一个标签显示一个标签』】【2，表示隔两个标签显示一个标签】，以次类推
                            interval: 0, //倾斜度 -90 至 90 默认为0
                            margin: 10, //刻度标签与轴线之间的距离。
                            formatter: '{value} %', //格式化刻度值
                            textStyle: { //类目标签的文字样式。
                                color: '#797979', //文字的颜色。
                                fontStyle: 'normal' //文字的字体系列
                            }
                        },

                        axisLine: { //坐标轴轴线相关设置。
                            lineStyle: {
                                type: 'solid', //坐标轴线线的类型。
                                color: '#efefef', //坐标轴线线的颜色。
                                width: '0' //坐标轴线线宽。
                            }
                        },
                        splitLine: { //坐标轴在 grid 区域中的分隔线。
                            show: true, //是否显示分隔线。默认数值轴显示，类目轴不显示。
                            lineStyle: {
                                type: 'dashed', //分隔线线的类型。可选：【'solid'】【'dashed'】【'dotted'】
                                color: ['#ccc'], //分隔线颜色，可以设置成单个颜色。也可以设置成颜色数组，分隔线会按数组中颜色的顺序依次循环设置颜色。
                            }
                        },
                        show: true, //是否显示 y 轴。
                    }
                ],
                label: {
                    "normal": {
                        "show": true,
                        "formatter": function(param) {
                            return param.value.toFixed(2) + '%';
                        },
                        "position": "top"
                    },

                },
                series: [ //系列列表
                    {
                        name: '', //系列名称，用于tooltip的显示，legend 的图例筛选
                        type: 'bar', //类型
                        barWidth: '60%', //柱条的宽度，不设时自适应。支持设置成相对于类目宽度的百分比。
                        barMaxWidth: 25, //柱条的最大宽度，不设时自适应。支持设置成相对于类目宽度的百分比。
                        itemStyle: {
                            normal: {
                                color: function(params) { //柱条的颜色。
                                    // build a color map as your need.
                                    var colorList = [
                                        '#F0D7B0', '#F47958', '#AA93F6', '#FFB062', '#FFD278', '#DC9FFF', '#CDB29F', '##F7C3C9', '#cab5e0', '#f28c63'
                                    ];
                                    return colorList[params.dataIndex]
                                },
                                label: {
                                    show: true, //是否显示标签。
                                    position: 'top', //标签的位置。可选【'top'】【'left'】【'right'】【'bottom'】【'inside'】【'insideLeft'】【'insideRight'】【'insideTop'】【'insideBottom'】【'insideTopLeft'】【'insideBottomLeft'】【'insideTopRight'】【'insideBottomRight'】
                                    formatter: '{c}%' //格式化显示
                                }
                            }
                        },
                        label: {
                            normal: {
                                textStyle: { //文本样式
                                    color: '#797979' //颜色
                                }
                            }
                        },
                        data: [] //系列中的数据内容数组。数组项通常为具体的数据项。
                    }
                ]
            },
            //折线图配置项和数据
            line: {
                color: '', //颜色
                title: { //图表标题
                    text: '',
                    subtext: ''
                },
                tooltip: { //提示框组件。
                    trigger: 'axis'
                },
                legend: { //图例设置
                    data: []
                },
                toolbox: { //图表工具箱
                    show: false,
                    feature: {
                        magicType: {
                            show: true,
                            type: ['stack', 'tiled']
                        },
                        saveAsImage: {
                            show: true
                        }
                    }
                },
                xAxis: { //x轴配置
                    type: 'category',
                    boundaryGap: true,
                    axisTick: { //坐标轴刻度相关设置。
                        alignWithLabel: true //类目轴中在 boundaryGap 为 true 的时候有效，可以保证刻度线和标签对齐。
                    },
                    data: []
                },
                yAxis: { //y轴配置
                    type: 'value'
                },
                grid: [{ //画布大小
                    left: '10%',
                    top: '10%',
                    right: '5%',
                    bottom: '10%'
                }],
                series: []
            },
            //饼图配置项和数据
            pie: {
                color: "", //颜色
                tooltip: { //提示框组件。
                    trigger: 'item',
                    formatter: function(param) {
                        return param.seriesName + ': ' + param.data.name + '<br/>人数：' + param.data.number + '<br/> 占比：' + param.data.value + '%';
                    }
                },
                legend: { //图例配置
                    padding: 5,
                    bottom: '2%',
                    data: []
                },
                grid: {
                    left: '50%'
                },
                series: [{
                    name: '',
                    type: 'pie',
                    radius: '50%',
                    center: ['50%', '50%'],
                    data: [],
                    avoidLabelOverlap: true,
                    itemStyle: {
                        normal: {
                            label: {
                                show: true,
                                formatter: function(param) {
                                    // console.log(param);
                                    return param.data.name + '，' + param.percent.toFixed(2) + '%';
                                },
                                textStyle: {
                                    color: '#000'
                                }

                            },
                            labelLine: {
                                show: true,
                                smooth: 0.2,
                                length: 0,
                                length2: 10
                            }
                        },
                        emphasis: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            },
            //引入地图
            mapType: '',
            //地图配置和数据
            mapAndOther: {
                tooltip: {
                    trigger: 'item',
                    formatter: function(param) {
                        // console.log(param);
                        return '' + param.name + '<br/>人数：' + EchartObj.api.formatter.p[param.name] + '<br/>比例：' + param.value.toFixed(2) + '%';
                    }
                },
                visualMap: {
                    min: 0,
                    max: 100,
                    left: 'left',
                    top: 'bottom',
                    text: ['高', '低'], // 文本，默认为数值文本
                    calculable: false,
                    dimension: 0,
                    colorLightness: [0.2, 100],
                    color: ['#c05050', '#e5cf0d', '#5ab1ef'],
                    formatter: function(param) {
                        return param.toFixed(2) + '%';
                        // console.log(param);
                    }
                },
                grid: [{
                    left: '60%',
                    right: '10%',
                    top: '10%',
                    height: 280, //设置grid高度
                    containLabel: true
                }],
                xAxis: [{
                    type: 'value',
                    axisLabel: {
                        show: false
                    },
                    axisTick: {
                        show: false
                    },
                    axisLine: {
                        show: false
                    },
                    splitLine: {
                        show: false
                    }

                }],
                yAxis: [{
                    type: 'category',
                    boundaryGap: true,
                    axisTick: {
                        show: true
                    },
                    axisLabel: {
                        interval: null
                    },
                    data: [],
                    splitLine: {
                        show: false
                    }
                }],
                series: [{
                    name: '',
                    type: 'map',
                    mapType: "",
                    left: '5%',
                    roam: false,
                    label: {
                        normal: {
                            show: true
                        },
                        emphasis: {
                            show: true
                        }
                    },
                    data: []
                }, {
                    name: '',
                    type: 'bar',
                    label: {
                        "normal": {
                            "show": true,
                            formatter: function(param) {
                                return param.value.toFixed(2) + '%';
                            },
                            "position": "right"
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#ff955f'
                        }
                    },
                    data: []
                }]
            },
            //散点图
            scatter: {
                backgroundColor: '#F2F2F2',
                color: [
                    '#FF8259', '#DBA853', '#FCACEE', '#C575EE', '#90A2FE', '#86CF88', '#76D9C8', '#64B3CA'
                ],
                legend: {
                    type: 'scroll',
                    orient: 'vertical',
                    left: '3%',
                    top: '10%',
                    bottom: '10%',
                    itemGap: 30,
                    itemHeight: 20,
                    data: [],
                    textStyle: {
                        color: '#666666',
                        fontSize: 16
                    }

                },
                grid: {
                    x: '20%',
                    // x2: 150,
                    y: '10%',
                    // y2: '10%'
                },
                tooltip: {
                    padding: 10,
                    backgroundColor: '#222',
                    borderColor: '#777',
                    borderWidth: 1,
                    formatter: function(param) {
                        console.log(param)
                        var mydate = new Date();
                        mydate = mydate.valueOf();
                        mydate = mydate - param.data[0] * 24 * 60 * 60 * 1000
                        mydate = new Date(mydate)
                        var dd = mydate.getFullYear() + "-" + (mydate.getMonth() + 1) + "-" + mydate.getDate();
                        return param.seriesName + ',' + ' R: ' + dd + ', F: ' + param.data[1] + ', V: ' + param.data[2] + ' 人数: ' + param.data[3];
                    }
                },
                xAxis: {
                    type: 'value',
                    name: '',
                    nameGap: 16,
                    nameTextStyle: {
                        color: '#666666',
                        fontSize: 14
                    },
                    // max: 31,
                    splitLine: {
                        show: false
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#B3B3B3'
                        }
                    },
                    axisLabel: {
                        formatter: function(res) {
                            var mydate = new Date();
                            mydate = mydate.valueOf();
                            mydate = mydate - res * 24 * 60 * 60 * 1000
                            mydate = new Date(mydate)
                            var dd = mydate.getFullYear() + "-" + (mydate.getMonth() + 1) + "-" + mydate.getDate();
                            return dd;
                        }
                    }
                },
                yAxis: {
                    type: '',
                    name: '',
                    nameLocation: 'end',
                    nameGap: 20,
                    nameTextStyle: {
                        color: '#666666',
                        fontSize: 16
                    },
                    axisLine: {
                        lineStyle: {
                            color: '#B3B3B3'
                        }
                    },
                    splitLine: {
                        show: false
                    }
                },
                series: []
            }
        },
        api: {
            //柱形图配置
            barConfig: {},
            //饼图配置
            pieConfig: {},
            //折线图配置
            lineConfig: {},
            //地图配置
            mapConfig: {},
            //散点图配置
            scatterConfig: {},
            formatter: {
                toThousands: function() {
                    var result = [],
                        counter = 0;
                    num = (num || 0).toString().split('');
                    for (var i = num.length - 1; i >= 0; i--) {
                        counter++;
                        result.unshift(num[i]);
                        if (!(counter % 3) && i != 0) {
                            result.unshift(',');
                        }
                    }
                    return result.join('');
                },
                p: [],
            },
            //图表异步请求
            //ajaxOptions 为异步请求后台参数，chartOptions为图表参数
            ajax: function(ajaxOptions, chartOptions) {
                //请求方式改为get，获取数据不记录日志
                $.extend(true, ajaxOptions, {
                    type: 'get'
                });
                console.log(chartOptions.type);

                //图表loading效果
                $('#' + chartOptions.targetId).html('<img style="position: absolute;width: 60px;height: 60px;left: 50%;top: 50%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);border:none;" src="/assets/img/loading_echarts.gif" />');
                Backend.api.ajax(ajaxOptions, function(res) {
                    if (chartOptions.type == 'line') {
                        EchartObj.api.lineConfig = EchartObj.config.line;
                        //置空数据配置，独立没个图表的配置，防止初始配置被污染
                        EchartObj.api.lineConfig.legend.data = [];
                        EchartObj.api.lineConfig.xAxis.data = [];
                        EchartObj.api.lineConfig.series = [];
                        $.extend(true, EchartObj.api.lineConfig, {
                            targetId: chartOptions.targetId,
                            downLoadID: chartOptions.downLoadID,
                            downLoadTitle: chartOptions.downLoadTitle
                        }, {
                            legend: {
                                data: res.column
                            },
                            xAxis: {
                                data: res.xcolumnData
                            },
                            series: res.columnData
                        });
                    } else if (chartOptions.type == 'bar') {
                        EchartObj.api.barConfig = EchartObj.config.bar;
                        //置空数据配置，独立没个图表的配置，防止初始配置被污染
                        EchartObj.api.barConfig.legend.data = [];
                        EchartObj.api.barConfig.xAxis.data = [];
                        EchartObj.api.barConfig.series = [];
                        console.log(res.columnData);
                        //单类目数据转换
                        if (chartOptions.dataType == 'single') {
                            //转换数据格式
                            var sdata = [];
                            var ss = [];
                            for (var i in res.columnData) {
                                sdata[i] = {
                                    name: res.columnData[i].name,
                                    type: 'bar',
                                    data: [res.columnData[i].value]
                                };
                                ss[res.columnData[i].name] = res.columnData[i].number
                            }
                            console.log('数字：', ss)
                            $.extend(true, EchartObj.api.barConfig, chartOptions.bar, {
                                targetId: chartOptions.targetId,
                                downLoadID: chartOptions.downLoadID,
                                downLoadTitle: chartOptions.downLoadTitle
                            }, {
                                tooltip: {
                                    trigger: 'item',
                                    formatter: function(param, re) { //格式化提示信息
                                        console.log(param);
                                        console.log(re)
                                        return param.seriesName + '<br/>人数：' + ss[param.seriesName] + '<br/> 占比：' + param.data + '%';
                                    }
                                },
                                legend: {
                                    data: res.column
                                },
                                xAxis: {
                                    data: []
                                },
                                series: sdata
                            });
                        } else {
                            console.log(res.columnName)
                            if (res.columnName) {
                                chartOptions.bar.yAxis.data = res.columnName[chartOptions.columnName]
                            }
                            $.extend(true, EchartObj.api.barConfig, chartOptions.bar, {
                                targetId: chartOptions.targetId,
                                downLoadID: chartOptions.downLoadID,
                                downLoadTitle: chartOptions.downLoadTitle
                            }, {
                                legend: {
                                    data: res.column
                                },
                                series: res.columnData
                            });
                        }
                    } else if (chartOptions.type == 'pie') {
                        EchartObj.api.pieConfig = EchartObj.config.pie;
                        //置空数据配置，独立没个图表的配置，防止初始配置被污染
                        EchartObj.api.pieConfig.legend.data = [];
                        EchartObj.api.pieConfig.series[0].data = [];
                        $.extend(true, EchartObj.api.pieConfig, chartOptions.pie, {
                            targetId: chartOptions.targetId,
                            downLoadID: chartOptions.downLoadID,
                            downLoadTitle: chartOptions.downLoadTitle
                        }, {
                            legend: {
                                data: res.column
                            },
                            series: [{
                                data: res.columnData
                            }]
                        });
                    } else if (chartOptions.type == 'mapAndOther') {
                        EchartObj.api.mapConfig = EchartObj.config.mapAndOther;
                        EchartObj.api.formatter.p = res.p;
                        EchartObj.api.mapConfig.mapType = (chartOptions.mapType && chartOptions.mapType != undefined) ? 'map/' + chartOptions.mapType : 'map/' + (res.en_name ? res.en_name : 'china');
                        $.extend(true, EchartObj.api.mapConfig, {
                            targetId: chartOptions.targetId,
                            downLoadID: chartOptions.downLoadID,
                            downLoadTitle: chartOptions.downLoadTitle
                        }, {
                            yAxis: [{
                                data: res.name_data
                            }],
                            series: [{
                                type: 'map',
                                name: '',
                                mapType: res.tag,
                                left: '15%',
                                roam: false,
                                label: {
                                    normal: {
                                        show: true
                                    },
                                    emphasis: {
                                        show: true
                                    }
                                },
                                data: res.map_data
                            }, {
                                data: res.line_data
                            }]
                        })
                    } else if (chartOptions.type == 'scatter') {
                        EchartObj.api.scatterConfig = EchartObj.config.scatter;
                        //置空数据配置，独立没个图表的配置，防止初始配置被污染
                        EchartObj.api.scatterConfig.legend.data = [];
                        EchartObj.api.scatterConfig.series = [];
                        //构建图表需要数据
                        var seriesData = [];
                        for (var i in res.columnData) {
                            seriesData[i] = {
                                name: res.columnData[i].name,
                                data: res.columnData[i].data,
                                type: 'scatter',
                                symbolSize: function(res) { //点的大小
                                    return Math.sqrt(res[2]) / 4;
                                    // console.log(res[2]);
                                },
                                label: {
                                    emphasis: { //鼠标放到散点图上，显示的内容
                                        show: true,
                                        formatter: function(param) {
                                            return param.data[4];
                                        },
                                        position: 'top'
                                    }
                                },
                                itemStyle: {
                                    normal: {
                                        shadowBlur: 10,
                                        shadowColor: 'rgba(25, 100, 150, 0.5)',
                                        shadowOffsetY: 5,
                                        // color: '#D15FEE'
                                    }
                                }
                            }
                        }
                        $.extend(true, EchartObj.api.scatterConfig, chartOptions.scatter, {
                            targetId: chartOptions.targetId,
                            downLoadID: chartOptions.downLoadID,
                            downLoadTitle: chartOptions.downLoadTitle
                        }, {
                            legend: {
                                data: res.column
                            },
                            series: seriesData
                        });
                    }
                    //执行图表方法
                    eval("EchartObj.api." + chartOptions.type + "()");
                    return false;
                })
            },
            //图表自适应窗口变化
            resizeChart(obj) {
                window.addEventListener('resize', function() {
                    obj.resize();
                });
            },
            //判断浏览器
            myBrowser: function() {
                var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
                var isOpera = userAgent.indexOf("OPR") > -1;
                if (isOpera) {
                    return "Opera"
                }; //判断是否Opera浏览器 OPR/43.0.2442.991

                if (userAgent.indexOf("Firefox") > -1) {
                    return "FF";
                } //判断是否Firefox浏览器  Firefox/51.0
                if (userAgent.indexOf("Trident") > -1) {
                    return "IE";
                } //判断是否IE浏览器  Trident/7.0; rv:11.0
                if (userAgent.indexOf("Edge") > -1) {
                    return "Edge";
                } //判断是否Edge浏览器  Edge/14.14393
                if (userAgent.indexOf("Chrome") > -1) {
                    return "Chrome";
                } // Chrome/56.0.2924.87
                if (userAgent.indexOf("Safari") > -1) {
                    return "Safari";
                } //判断是否Safari浏览
            },
            //转换图片
            base64Img2Blob: function(code) {
                var parts = code.split(';base64,');
                var contentType = parts[0].split(':')[1];
                var raw;
                if (window.atob) {
                    raw = window.atob(parts[1]);
                    var rawLength = raw.length;
                    var uInt8Array = new Uint8Array(rawLength);
                    for (var i = 0; i < rawLength; ++i) {
                        uInt8Array[i] = raw.charCodeAt(i);
                    }
                    return new Blob([uInt8Array], {
                        type: contentType
                    });
                } else {
                    raw = BaseCode(parts[1]);
                }
            },
            //下载文件
            downloadFile: function(fileName, content) {
                var blob = this.base64Img2Blob(content);
                // 支持IE11  base64Img2Blob
                window.navigator.msSaveBlob(blob, fileName);
            },
            //保存图表图片
            //tag为下载按钮ID，mychart为图表对象，image_name图表下载名称
            SaveImg: function(tag, mychart, image_name) {
                var _this = this;
                $(tag).click(function() {
                    var aTag = document.createElement("a");
                    var dataurl = mychart.getDataURL({
                        type: 'png'
                    });

                    console.log(_this.myBrowser());

                    if (_this.myBrowser() == "IE") {
                        aTag.href = "#";
                        _this.downloadFile(image_name + '.png', dataurl);
                    } else {
                        var MIME_TYPE = 'image/png';
                        aTag.href = dataurl;
                        aTag.target = "_self";
                        aTag.download = image_name + '.png';
                    }

                    document.body.appendChild(aTag);
                    aTag.click();
                    document.body.removeChild(aTag);
                });
            },
            //柱形图
            bar: function() {
                // 指定图表的配置项和数据
                var option = EchartObj.api.barConfig;
                // 基于准备好的dom，初始化echarts实例
                var myChart = Echarts.init(document.getElementById(option.targetId), EchartObj.config.theme);
                //如果未配置颜色，则写入默认颜色配置
                if (!option.color) {
                    $.extend(true, option, {
                        color: EchartObj.config.echarsColors
                    });
                }
                console.log(option)
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
                this.resizeChart(myChart);
                //下载图表，image_name为图表名称
                var image_name = option.downLoadTitle ? option.downLoadTitle : '柱形图';
                this.SaveImg(option.downLoadID, myChart, image_name);
                return myChart;
            },
            //折线图
            line: function() {
                // 指定图表的配置项和数据
                var option = EchartObj.api.lineConfig;
                // 基于准备好的dom，初始化echarts实例
                var myChart = Echarts.init(document.getElementById(option.targetId), EchartObj.config.theme);
                //如果未配置颜色，则写入默认颜色配置
                if (!option.color) {
                    $.extend(true, option, {
                        color: EchartObj.config.echarsColors
                    });
                }

                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
                this.resizeChart(myChart);
                //下载图表，image_name为图表名称
                var image_name = option.downLoadTitle ? option.downLoadTitle : '折线图';
                this.SaveImg(option.downLoadID, myChart, image_name);
                return myChart;
            },
            //饼图
            pie: function() {
                // 指定图表的配置项和数据
                var option = EchartObj.api.pieConfig;
                // 基于准备好的dom，初始化echarts实例
                var myChart = Echarts.init(document.getElementById(option.targetId), EchartObj.config.theme);

                //如果未配置颜色，则写入默认颜色配置
                if (!option.color) {
                    $.extend(true, option, {
                        color: EchartObj.config.echarsColors
                    });
                }

                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
                this.resizeChart(myChart);
                //下载图表，image_name为图表名称
                var image_name = option.downLoadTitle ? option.downLoadTitle : '饼图';
                this.SaveImg(option.downLoadID, myChart, image_name);
                return myChart;
            },
            //地图和其他图
            mapAndOther: function() {
                // 指定图表的配置项和数据
                var option = EchartObj.config.mapAndOther;
                // 基于准备好的dom，初始化echarts实例
                var myChart = Echarts.init(document.getElementById(option.targetId), EchartObj.config.theme);
                console.log(option);
                console.log(123123);
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
                this.resizeChart(myChart);
                //下载图表，image_name为图表名称
                var image_name = option.downLoadTitle ? option.downLoadTitle : '地图';
                this.SaveImg(option.downLoadID, myChart, image_name);
                return myChart;
            },
            //散点图
            scatter: function() {
                // 指定图表的配置项和数据
                var option = EchartObj.api.scatterConfig;
                // 基于准备好的dom，初始化echarts实例
                var myChart = Echarts.init(document.getElementById(option.targetId), EchartObj.config.theme);

                //如果未配置颜色，则写入默认颜色配置
                if (!option.color) {
                    $.extend(true, option, {
                        color: EchartObj.config.echarsColors
                    });
                }
                console.log(option);
                // 使用刚指定的配置项和数据显示图表。
                myChart.setOption(option);
                this.resizeChart(myChart);
                //下载图表，image_name为图表名称
                var image_name = option.downLoadTitle ? option.downLoadTitle : '散点图';
                this.SaveImg(option.downLoadID, myChart, image_name);
                return myChart;
            }
        }
    }
    return EchartObj;
})
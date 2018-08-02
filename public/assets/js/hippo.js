define(['jquery', 'bootstrap', 'toastr', 'layer', 'bootstrap-daterangepicker'], function($, undefined, Toastr, Layer, Picker) {
    var Hippo = {
        config: {
            toastr: {
                "closeButton": true, //关闭按钮
                "debug": false, //调试模式
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-top-center",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut",
            }
        },
        events: {
            //请求成功的回调
            onAjaxSuccess: function(ret, onAjaxSuccess) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作成功!';

                if (typeof onAjaxSuccess === 'function') {
                    var result = onAjaxSuccess.call(this, data, ret);
                    if (result === false)
                        return;
                }
                Toastr.success(msg);
            },
            //请求错误的回调
            onAjaxError: function(ret, onAjaxError) {
                var data = typeof ret.data !== 'undefined' ? ret.data : null;
                if (typeof onAjaxError === 'function') {
                    var result = onAjaxError.call(this, data, ret);
                    if (result === false) {
                        return;
                    }
                }
                Toastr.error(ret.msg);
                //+ "(code:" + ret.code + ")"
            },
            //服务器响应数据后
            onAjaxResponse: function(response) {
                try {
                    var ret = typeof response === 'object' ? response : JSON.parse(response);
                    if (!ret.hasOwnProperty('code')) {
                        $.extend(ret, {
                            code: -2,
                            msg: response,
                            data: null
                        });
                    }
                } catch (e) {
                    var ret = {
                        code: -1,
                        msg: e.message,
                        data: null
                    };
                }
                return ret;
            }
        },
        api: {
            picker: Picker,
            //ajax方法
            ajax: function(options, success, error) {
                options = typeof options === 'string' ? {
                    url: options
                } : options;
                var index = Layer.load();
                options = $.extend({
                    type: "POST",
                    dataType: "json",
                    success: function(ret) {
                        Layer.close(index);
                        ret = Hippo.events.onAjaxResponse(ret);
                        if (ret.code === 1) {
                            Hippo.events.onAjaxSuccess(ret, success);
                        } else {
                            Hippo.events.onAjaxError(ret, error);
                        }
                    },
                    error: function(xhr) {
                        Layer.close(index);
                        var ret = {
                            code: xhr.status,
                            msg: xhr.statusText,
                            data: null
                        };
                        Hippo.events.onAjaxError(ret, error);
                    }
                }, options);
                console.log(options)
                $.ajax(options);
            },
            //修复URL
            fixurl: function(url) {
                if (url.substr(0, 1) !== "/") {
                    var r = new RegExp('^(?:[a-z]+:)?//', 'i');
                    if (!r.test(url)) {
                        url = Config.moduleurl + "/" + url;
                    }
                }
                return url;
            },
            //获取修复后可访问的cdn链接
            cdnurl: function(url) {
                return /^(?:[a-z]+:)?\/\//i.test(url) ? url : Config.upload.cdnurl + url;
            },
            //查询Url参数
            query: function(name, url) {
                if (!url) {
                    url = window.location.href;
                }
                name = name.replace(/[\[\]]/g, "\\$&");
                var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                    results = regex.exec(url);
                if (!results)
                    return null;
                if (!results[2])
                    return '';
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            },
            //打开一个弹出窗口
            open: function(url, title, options) {
                title = title ? title : "查看";
                console.log(url)
                url = Hippo.api.fixurl(url);
                console.log(url)
                url = url + (url.indexOf("?") > -1 ? "&" : "?") + "dialog=1";
                console.log(url)
                var area = [$(window).width() > 800 ? '800px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
                options = $.extend({
                    type: 2, // 弹窗类型0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
                    title: title, // 弹窗标题
                    shadeClose: true, // 控制点击弹层外区域关闭
                    shade: [0.1, '#000'], //是否开启遮罩层
                    maxmin: true, // 该参数值对type:1和type:2有效。默认不显示最大小化按钮
                    moveOut: true, // 默认只能在窗口内拖拽，如果你想让拖到窗外，那么设定moveOut: true
                    area: area, //宽高设置，[宽，高]
                    content: url, //弹窗内容
                    zIndex: Layer.zIndex, //层叠顺序，一般用于解决和其它组件的层叠冲突
                    success: function(layero, index) { //层弹出后的成功回调方法
                        var that = this;
                        //存储callback事件
                        $(layero).data("callback", that.callback);
                        //$(layero).removeClass("layui-layer-border");
                        Layer.setTop(layero);
                        var frame = Layer.getChildFrame('html', index);
                        var layerfooter = frame.find(".layer-footer");
                        Hippo.api.layerfooter(layero, index, that);

                        //绑定事件
                        if (layerfooter.size() > 0) {
                            // 监听窗口内的元素及属性变化
                            // Firefox和Chrome早期版本中带有前缀
                            var MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver
                            // 选择目标节点
                            var target = layerfooter[0];
                            // 创建观察者对象
                            var observer = new MutationObserver(function(mutations) {
                                Hippo.api.layerfooter(layero, index, that);
                                mutations.forEach(function(mutation) {});
                            });
                            // 配置观察选项:
                            var config = {
                                attributes: true,
                                childList: true,
                                characterData: true,
                                subtree: true
                            }
                            // 传入目标节点和观察选项
                            observer.observe(target, config);
                            // 随后,你还可以停止观察
                            // observer.disconnect();
                        }
                    }
                }, options ? options : {});
                if ($(window).width() < 480 || (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream && top.$(".tab-pane.active").size() > 0)) {
                    options.area = [top.$(".tab-pane.active").width() + "px", top.$(".tab-pane.active").height() + "px"];
                    options.offset = [top.$(".tab-pane.active").scrollTop() + "px", "0px"];
                }
                Layer.open(options);
                return false;
            },
            //关闭窗口并回传数据
            close: function(data) {
                var index = parent.Layer.getFrameIndex(window.name);
                var callback = parent.$("#layui-layer" + index).data("callback");
                //再执行关闭
                parent.Layer.close(index);
                //再调用回传函数
                if (typeof callback === 'function') {
                    callback.call(undefined, data);
                }
            },
            layerfooter: function(layero, index, that) {
                var frame = Layer.getChildFrame('html', index);
                var layerfooter = frame.find(".layer-footer");
                if (layerfooter.size() > 0) {
                    $(".layui-layer-footer", layero).remove();
                    var footer = $("<div />").addClass('layui-layer-btn layui-layer-footer');
                    footer.html(layerfooter.html());
                    if ($(".row", footer).size() === 0) {
                        $(">", footer).wrapAll("<div class='row'></div>");
                    }
                    footer.insertAfter(layero.find('.layui-layer-content'));
                    //绑定事件
                    footer.on("click", ".btn", function() {
                        if ($(this).hasClass("disabled") || $(this).parent().hasClass("disabled")) {
                            return;
                        }
                        $(".btn:eq(" + $(this).index() + ")", layerfooter).trigger("click");
                    });

                    var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                    var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                    //重设iframe高度
                    $("iframe", layero).height(layero.height() - titHeight - btnHeight);
                }
                //修复iOS下弹出窗口的高度和iOS下iframe无法滚动的BUG
                if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                    var titHeight = layero.find('.layui-layer-title').outerHeight() || 0;
                    var btnHeight = layero.find('.layui-layer-btn').outerHeight() || 0;
                    $("iframe", layero).parent().css("height", layero.height() - titHeight - btnHeight);
                    $("iframe", layero).css("height", "100%");
                }
            },
            success: function(options, callback, time) {
                var type = typeof options === 'function';
                var time = time ? time : 3000;
                if (type) {
                    callback = options;
                }
                return Layer.msg('操作成功', $.extend({
                    time: time,
                    offset: 0,
                    icon: 1
                }, type ? {} : options), callback);
            },
            error: function(options, callback) {
                var type = typeof options === 'function';
                if (type) {
                    callback = options;
                }
                return Layer.msg('操作失败', $.extend({
                    time: 3000,
                    offset: 0,
                    icon: 2
                }, type ? {} : options), callback);
            },
            //layerBox
            LayerBox: function(type, title, con, shade, offset, area) {
                Layer.open({
                    type: type, //类型
                    title: title, //标题
                    closeBtn: 1, //关闭按钮
                    shade: shade, //遮罩
                    skin: 'LAYER', //自定义class
                    offset: offset, //弹窗坐标
                    area: area, //弹窗大小
                    id: 'layabox', //id
                    content: con, //这里content是一个普通的String
                    cancel: function() {
                        layer.closeAll();
                    }
                });
            },
            //关闭弹窗
            closeLayer: function() {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            },
            /*
                时间窗口生成
            options为查看配置项，callback为回调事件，例如
             function(start, end, label) {
                //格式化日期显示框
                 $('#searchDate').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
             }
             */
            TimePicker: function(options, callback, ele) {
                require(['bootstrap-daterangepicker'], function() {
                    var options_default = {
                        showDropdowns: true,
                        autoApply: true, //隐藏确认/取消按钮
                        linkedCalendars: true,
                        locale: { //语言设置
                            format: 'YYYY-MM-DD', //控件中from和to 显示的日期格式
                            applyLabel: '确定',
                            cancelLabel: '取消',
                            fromLabel: '起始时间',
                            toLabel: '结束时间',
                            separator: ' 至 ',
                            customRangeLabel: '自定义',
                            daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
                            monthNames: ['一月', '二月', '三月', '四月', '五月', '六月',
                                '七月', '八月', '九月', '十月', '十一月', '十二月'
                            ],
                            firstDay: 1
                        } //汉化日期控件
                    };
                    $.extend(true, options_default, options);
                    $('.daterangepicker' + ele).daterangepicker(options_default, callback);
                });
            },
            SelectPicker: function(ele, hiddenCallback) {
                require(['bootstrap-select', 'bootstrap-select-lang'], function() {
                    // console.log($.fn.selectpicker.defaults)
                    $('.selectpicker').selectpicker('refresh');
                    // $('.selectpicker').on('show.bs.select',refreshedCallback);
                    // $('.selectpicker').on('changed.bs.select',callback);
                    $('.selectpicker' + ele).on('hidden.bs.select', hiddenCallback);

                })
            },
            toastr: Toastr,
            layer: Layer
        },
        init: function() {
            //初始化
            // 对相对地址进行处理
            $.ajaxSetup({
                beforeSend: function(xhr, setting) {
                    setting.url = Hippo.api.fixurl(setting.url);
                }
            });
            Layer.config({
                skin: 'layui-layer-wind'
            });
            // 绑定ESC关闭窗口事件
            $(window).keyup(function(e) {
                if (e.keyCode == 27) {
                    if ($(".layui-layer").size() > 0) {
                        var index = 0;
                        $(".layui-layer").each(function() {
                            index = Math.max(index, parseInt($(this).attr("times")));
                        });
                        if (index) {
                            Layer.close(index);
                        }
                    }
                }
            });
            //设置Toastr配置
            Toastr.options = Hippo.config.toastr;
            console.log('1:' + Toastr.options);
            //处理全局二级按钮的面包屑导航
            $(document).on('click', '.breadCrumb', function() {
                var breadCrumb = $(this).attr('breadCrumb');
                localStorage.lastmenuName = breadCrumb;
            })
        },
    }
    //将Layer暴露到全局中去
    window.Layer = Layer;
    //将Toastr暴露到全局中去
    window.Toastr = Toastr;
    //将Hippo渲染至全局
    window.Hippo = Hippo;
    //初始化
    Hippo.init();
    return Hippo;
})
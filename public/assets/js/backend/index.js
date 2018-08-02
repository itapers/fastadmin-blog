define(['jquery', 'bootstrap', 'backend', 'adminlte', 'form', 'addtabs'],function($, undefined, Backend, AdminLTE, Form, undefined){
    var Controller = {
        index:function(){
            //快捷搜索
            // console.log($("form.sidebar-form > .input-group").width())
            $(".menuresult").width($("form.sidebar-form > .input-group").width());
            var isAndroid = /(android)/i.test(navigator.userAgent);
            var searchResult = $(".menuresult");
            $("form.sidebar-form").on("blur", "input[name=q]", function () {
                searchResult.addClass("hide");
                if (isAndroid) {
                    $.AdminLTE.options.sidebarSlimScroll = true;
                }
            }).on("focus", "input[name=q]", function () {
                if (isAndroid) {
                    $.AdminLTE.options.sidebarSlimScroll = false;
                }
                if ($("a", searchResult).size() > 0) {
                    searchResult.removeClass("hide");
                }
            }).on("keyup", "input[name=q]", function () {
                searchResult.html('');console.log('search')
                var val = $(this).val();
                var html = new Array();
                if (val != '') {
                    $("ul.sidebar-menu li a[addtabs]:not([href^='javascript:;'])").each(function () {
                        if ($("span:first", this).text().indexOf(val) > -1 || $(this).attr("py").indexOf(val) > -1 || $(this).attr("pinyin").indexOf(val) > -1) {
                            html.push('<a data-url="' + $(this).attr("href") + '" href="javascript:;">' + $("span:first", this).text() + '</a>');
                            if (html.length >= 100) {
                                return false;
                            }
                        }
                    });
                }
                $(searchResult).append(html.join(""));
                if (html.length > 0) {
                    searchResult.removeClass("hide");
                } else {
                    searchResult.addClass("hide");
                }
            });
            //快捷搜索点击事件
            $("form.sidebar-form").on('mousedown click', '.menuresult a[data-url]', function () {
                if(Config.mySet.single_multile == 'multile'){
                    Backend.api.addtabs($(this).data("url"));
                } else {
                    $('#main_iframe').attr('src',$(this).data('url'));
                }

                $('input[name=q]').val($(this).text());
            });
             // 右侧控制栏切换
             $("[data-controlsidebar]").on('click', function () {
                change_layout($(this).data('controlsidebar'));
                var slide = !AdminLTE.options.controlSidebarOptions.slide;
                AdminLTE.options.controlSidebarOptions.slide = slide;
                if (!slide)
                    $('.control-sidebar').removeClass('control-sidebar-open');
            });
            //切换左侧sidebar显示隐藏
            $(document).on("click fa.event.toggleitem", ".sidebar-menu li > a", function (e) {
                $(".sidebar-menu li").removeClass("active");
                //当外部触发隐藏的a时,触发父辈a的事件
                if (!$(this).closest("ul").is(":visible")) {
                    //如果不需要左侧的菜单栏联动可以注释下面一行即可
                    $(this).closest("ul").prev().trigger("click");
                }

                var visible = $(this).next("ul").is(":visible");
                if (!visible) {
                    $(this).parents("li").addClass("active");
                } else {
                }
                e.stopPropagation();
            });
            //窗口大小改变,修正主窗体最小高度
            $(window).resize(function () {
                $(".tab-addtabs").css("height", $(".content-wrapper").height() + "px");
                console.log(($('.navbar').width()-$('.navbar-custom-menu').width()-45))
                $('#nav').css('width',($('.navbar').width()-$('.navbar-custom-menu').width()-45) + 'px')
            });


            //双击重新加载页面
            $(document).on("dblclick", ".sidebar-menu li > a", function (e) {
                $("#con_" + $(this).attr("addtabs") + " iframe").attr('src', function (i, val) {
                    return val;
                });
                e.stopPropagation();
            });
            //绑定tabs事件
            if(Config.mySet.single_multile == 'multile'){
                $('#nav').addtabs({iframeHeight: "100%"});

                if ($("ul.sidebar-menu li.active a").size() > 0) {
                    $("ul.sidebar-menu li.active a").trigger("click");
                } else {
                    $("ul.sidebar-menu li a[url!='javascript:;']:first").trigger("click");
                }
            }

            if (Config.referer) {
                //刷新页面后跳到到刷新前的页面
                if(Config.mySet.single_multile == 'multile'){
                    Backend.api.addtabs(Config.referer);
                } else {
                    window.location.href = Config.referer;
                }
            }
            //清除缓存
            $(document).on('click', "[data-toggle='wipecache']", function () {
                $.ajax({
                    url: 'ajax/wipecache',
                    dataType: 'json',
                    cache: false,
                    success: function (ret) {
                        //判断是否有code属性
                        if (ret.hasOwnProperty("code")) {
                            var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                            if (ret.code === 1) {
                                console.log(msg ? msg : '清空缓存成功！')
                                Toastr.success(msg ? msg : '清空缓存成功！');
                            } else {
                                Toastr.error(msg ? msg : '清空缓存失败！');
                            }
                        } else {
                            Toastr.error('未知的数据格式！');
                        }
                    }, error: function () {
                        Toastr.error('网络错误！');
                    }
                });
            });
            //全屏事件
            $(document).on('click', "[data-toggle='fullscreen']", function () {
                var doc = document.documentElement;
                if ($(document.body).hasClass("full-screen")) {
                    $(document.body).removeClass("full-screen");
                    document.exitFullscreen ? document.exitFullscreen() : document.mozCancelFullScreen ? document.mozCancelFullScreen() : document.webkitExitFullscreen && document.webkitExitFullscreen();
                } else {
                    $(document.body).addClass("full-screen");
                    doc.requestFullscreen ? doc.requestFullscreen() : doc.mozRequestFullScreen ? doc.mozRequestFullScreen() : doc.webkitRequestFullscreen ? doc.webkitRequestFullscreen() : doc.msRequestFullscreen && doc.msRequestFullscreen();
                }
            });
            // 切换子菜单显示和菜单小图标的显示
            $("[data-menu]").on('click', function () {
                if ($(this).data("menu") == 'show-submenu') {
                    $("ul.sidebar-menu").toggleClass("show-submenu");
                } else {
                    $(".nav-addtabs").toggleClass("disable-top-badge");
                }
            });
            if ($('ul.nav-addtabs').hasClass('disable-top-badge')) {
                $("[data-menu='disable-top-badge']").attr('checked', 'checked');
            }
            // 初始化自动调整
            $(window).resize();

        },
        login: function () {
            //让错误提示框居中
            console.log(2)
            Hippo.config.toastr.positionClass = "toast-top-center";
            //本地验证未通过时提示
            $("#login-form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });
            //为表单绑定事件
            console.log(Form)
            Form.api.bindevent($("#login-form"), function (data) {
                // localStorage.setItem("lastlogin", JSON.stringify({id: data.id, username: data.username, avatar: data.avatar}));
                location.href = Backend.api.fixurl(data.url);
            });
        }
    }
    return Controller;
})
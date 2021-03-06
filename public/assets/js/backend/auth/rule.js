define([
    'jquery', 'bootstrap', 'backend', 'datatable', 'form', 'template'
], function($, undefined, Backend, dataTable, Form, Template) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            dataTable.api.init({
                "columns": [
                    { "needControlShow":true, "title": "ID", "data":'id'},
                    { "needControlShow":true, "title": "标题", "data":'title',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.title(data, row)
                        }
                    },
                    { "needControlShow":true, "title": "图标", "data":'icon',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.icon(data, row)
                        }
                    },
                    { "needControlShow":true, "title": "规则URL", "data":'name',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.name(data, row)
                        }
                    },
                    { "needControlShow":true, "title": "权重", "data":'weigh'},
                    { "needControlShow":true, "title": "状态", "data":'status',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.status(data, row)
                        }
                    },
                    { "needControlShow":true, "title": "菜单", "data":'ismenu',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.menu(data, row)
                        }
                    },
                    { "needControlShow":false, "title": '<a href="javascript:;" class="btn btn-success btn-xs btn-toggle"><i class="fa fa-chevron-up"></i></a>', "data":'id',
                        render: function(data, type, row, meta){
                            return Controller.api.formatter.subnode(data, row)
                        }
                    },
                    { "needControlShow":false, "title": "操作", "data":'id', "orderable": false,
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.operate('id',data)
                        }
                    }
                ],
                "ordering":false,
                "paging":false,
                "showExport": true,
                "exportIgnoreColumn":[0,3,8,9],
                "showColumn": true,
                "columnCheckbox":true,
                "extend": {
                    "index_url": "auth/rule/index",
                    "add_url": "auth/rule/add",
                    "edit_url": "auth/rule/edit",
                    "del_url": "auth/rule/del",
                    "table": "auth_rule"
                },
                "drawCallback": function(){
                    layer.closeAll();
                    $(dataTable.config.refreshbtn).find('i').removeClass('fa-spin')
                    //默认隐藏所有子节点
                    $(".btn-node-sub.disabled").closest("tr").hide();
                    //显示隐藏子节点
                    $(".btn-node-sub").off("click").on("click", function (e) {
                        var status = $(this).data("shown") ? true : false;
                        $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
                            $(this).closest("tr").toggle(!status);
                        });
                        $(this).data("shown", !status);
                        return false;
                    });
                }
            });

            var table = $("#table");

            //初始化表格
            var tableObj = table.DataTable()

            // 为表格绑定事件
            dataTable.api.bindevent(table, tableObj);//当内容渲染完成后

            
            
            // table.on("click", ".btn-node-sub", function (e) {
            //     console.log('abc')
            //     var status = $(this).data("shown") ? true : false;
            //     $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
            //         $(this).closest("tr").toggle(!status);
            //     });
            //     $(this).data("shown", !status);
            //     return false;
            // });
            // table.on('.table', function (e, settings, json, xhr) {
            //     //$("a.btn[data-id][data-pid][data-pid!=0]").closest("tr").hide();
            //     $(".btn-node-sub.disabled").closest("tr").hide();

            //     //显示隐藏子节点
            //     table.on("click", ".btn-node-sub", function (e) {
            //         console.log('abc')
            //         var status = $(this).data("shown") ? true : false;
            //         $("a.btn[data-pid='" + $(this).data("id") + "']").each(function () {
            //             $(this).closest("tr").toggle(!status);
            //         });
            //         $(this).data("shown", !status);
            //         return false;
            //     });

            // });
            //展开隐藏一级
            $(document.body).on("click", ".btn-toggle", function (e) {
                $("a.btn[data-id][data-pid][data-pid!=0].disabled").closest("tr").hide();
                var that = this;
                var show = $("i", that).hasClass("fa-chevron-down");
                $("i", that).toggleClass("fa-chevron-down", !show);
                $("i", that).toggleClass("fa-chevron-up", show);
                $("a.btn[data-id][data-pid][data-pid!=0]").not('.disabled').closest("tr").toggle(show);
                $(".btn-node-sub[data-pid=0]").data("shown", show);
            });
            //展开隐藏全部
            $(document.body).on("click", ".btn-toggle-all", function (e) {
                var that = this;
                var show = $("i", that).hasClass("fa-plus");
                $("i", that).toggleClass("fa-plus", !show);
                $("i", that).toggleClass("fa-minus", show);
                $(".btn-node-sub.disabled").closest("tr").toggle(show);
                $(".btn-node-sub").data("shown", show);
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            formatter: {
                title: function (value, row, index) {
                    return !row.ismenu ? "<span class='text-muted'>" + value + "</span>" : value;
                },
                name: function (value, row, index) {
                    return !row.ismenu ? "<span class='text-muted'>" + value + "</span>" : value;
                },
                menu: function (value, row, index) {
                    return "<a href='javascript:;' class='btn btn-" + (value ? "info" : "default") + " btn-xs btn-change' data-id='"
                            + row.id + "' data-params='ismenu=" + (value ? 0 : 1) + "'>" + (value ? '是': '否') + "</a>";
                },
                icon: function (value, row, index) {
                    return '<i class="' + value + '"></i>';
                },
                status: function(value, row, index){
                    return value == 'normal' ? '<span class="text-success"><i class="fa fa-circle"></i> 正常</span>' : '<span class="text-grey"><i class="fa fa-circle"></i> 隐藏</span>';
                },
                subnode: function (value, row, index) {
                    return '<a href="javascript:;" data-id="' + row['id'] + '" data-pid="' + row['pid'] + '" class="btn btn-xs '
                            + (row['haschild'] == 1 ? 'btn-success' : 'btn-default disabled') + ' btn-node-sub"><i class="fa fa-sitemap"></i></a>';
                }
            },
            bindevent: function () {
                var iconlist = [];
                Form.api.bindevent($("form[role=form]"));
                $(document).on('click', ".btn-search-icon", function () {
                    if (iconlist.length == 0) {
                        $.get("/assets/libs/font-awesome/less/variables.less", function (ret) {
                            var exp = /fa-var-(.*):/ig;
                            var result;
                            while ((result = exp.exec(ret)) != null) {
                                iconlist.push(result[1]);
                            }
                            Layer.open({
                                type: 1,
                                area: ['460px', '300px'], //宽高
                                content: Template('chooseicontpl', {iconlist: iconlist})
                            });
                        });
                    } else {
                        Layer.open({
                            type: 1,
                            area: ['460px', '300px'], //宽高
                            content: Template('chooseicontpl', {iconlist: iconlist})
                        });
                    }
                });
                $(document).on('click', '#chooseicon ul li', function () {
                    $("input[name='row[icon]']").val('fa fa-' + $(this).data("font"));
                    Layer.closeAll();
                });
                $(document).on('keyup', 'input.js-icon-search', function () {
                    $("#chooseicon ul li").show();
                    if ($(this).val() != '') {
                        $("#chooseicon ul li:not([data-font*='" + $(this).val() + "'])").hide();
                    }
                });
            }
        }
    };
    return Controller;
});
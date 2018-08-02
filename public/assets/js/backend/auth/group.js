define([
    'jquery', 'bootstrap', 'backend', 'datatable', 'form', 'jstree'
], function($, undefined, Backend, dataTable, Form, undefined) {
    //读取选中的条目
    $.jstree.core.prototype.get_all_checked = function (full) {
        var obj = this.get_selected(), i, j;
        for (i = 0, j = obj.length; i < j; i++) {
            obj = obj.concat(this.get_node(obj[i]).parents);
        }
        obj = $.grep(obj, function (v, i, a) {
            return v != '#';
        });
        obj = obj.filter(function (itm, i, a) {
            return i == a.indexOf(itm);
        });
        return full ? $.map(obj, $.proxy(function (i) {
            return this.get_node(i);
        }, this)) : obj;
    };
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            dataTable.api.init({
                "columns": [
                    { "needControlShow":true, "title": "ID", "data":'id'
                    },
                    { "needControlShow":true, "title": "父级", "data":'pid'},
                    { "needControlShow":true, "title": "角色名称", "data":'name'},
                    { "needControlShow":true, "title": "状态", "data":'status',
                        render: function(data) {
                            return Controller.api.formatter.status(data)
                        }
                    },
                    { "needControlShow":false, "title": "操作", "data":'id', "orderable": false,
                        render:function(data, type, row, meta){
                            if (Config.admin.group_ids.indexOf(parseInt(data)) > -1) {
                                return '';
                            }
                            return dataTable.api.formatter.operate('id',data)
                        }
                    }
                ],
                "paging": false,
                "showExport": true,
                "exportIgnoreColumn":[0,5],
                "showColumn": true,
                "columnCheckbox":true,
                "extend": {
                    "index_url": "auth/group/index",
                    "add_url": "auth/group/add",
                    "edit_url": "auth/group/edit",
                    "del_url": "auth/group/del",
                    "multi_url": "auth/group/multi",
                }
            });

            var table = $("#table");

            //初始化表格
            var tableObj = table.DataTable()

            // 为表格绑定事件
            dataTable.api.bindevent(table, tableObj);//当内容渲染完成后

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            formatter: {
                status: function(value, row, index) {
                    return value == 'normal' ? '<span class="text-success"><i class="fa fa-circle"></i> 正常</span>' : '<span class="text-grey"><i class="fa fa-circle"></i> 隐藏</span>';
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"), null, null, function () {
                    if ($("#treeview").size() > 0) {
                        var r = $("#treeview").jstree("get_all_checked");
                        $("input[name='row[rules]']").val(r.join(','));
                    }
                    return true;
                });
                //渲染权限节点树
                //变更级别后需要重建节点树
                $(document).on("change", "select[name='row[pid]']", function () {
                    var pid = $(this).data("pid");
                    var id = $(this).data("id");
                    if ($(this).val() == id) {
                        $("option[value='" + pid + "']", this).prop("selected", true).change();
                        Backend.api.toastr.error('父组别不能是它的子组别!');
                        return false;
                    }
                    $.ajax({
                        url: "auth/group/roletree",
                        type: 'post',
                        dataType: 'json',
                        data: {id: id, pid: $(this).val()},
                        success: function (ret) {
                            if (ret.hasOwnProperty("code")) {
                                var data = ret.hasOwnProperty("data") && ret.data != "" ? ret.data : "";
                                if (ret.code === 1) {
                                    //销毁已有的节点树
                                    $("#treeview").jstree("destroy");
                                    Controller.api.rendertree(data);
                                } else {
                                    Backend.api.toastr.error(ret.data);
                                }
                            }
                        }, error: function (e) {
                            Backend.api.toastr.error(e.message);
                        }
                    });
                });
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#treeview").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#treeview").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $("select[name='row[pid]']").trigger("change");
            },
            rendertree: function (content) {
                $("#treeview")
                        .on('redraw.jstree', function (e) {
                            $(".layer-footer").attr("domrefresh", Math.random());
                        })
                        .jstree({
                            "themes": {"stripes": true},
                            "checkbox": {
                                "keep_selected_style": false,
                            },
                            "types": {
                                "root": {
                                    "icon": "fa fa-folder-open",
                                },
                                "menu": {
                                    "icon": "fa fa-folder-open",
                                },
                                "file": {
                                    "icon": "fa fa-file-o",
                                }
                            },
                            "plugins": ["checkbox", "types"],
                            "core": {
                                'check_callback': true,
                                "data": content
                            }
                        });
            }
        }
    };
    return Controller;
});
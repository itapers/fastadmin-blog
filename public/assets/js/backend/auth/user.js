define(['jquery', 'bootstrap', 'backend', 'datatable', 'form'], function ($, undefined, Backend, dataTable, Form) {

    var Controller = {
        index: function () {
            dataTable.api.init({
                "columns": [
                    { "needControlShow":true, "title": "序号", "data":null,
                        render:function(data, type, row, meta){
                            // 显示行号
                            var startIndex = meta.settings._iDisplayStart;
                            return startIndex + meta.row + 1;
                        }
                    },
                    { "needControlShow":true, "title": "用户名", "data":'username'},
                    { "needControlShow":true, "title": "昵称", "data":'nickname'},
                    { "needControlShow":true, "title": "角色组", "data":'groups_text'},
                    { "needControlShow":true, "title": "邮箱", "data":'email'},
                    { "needControlShow":true, "title": "状态", "data":'status',
                        render:function(data, type, row, meta){
                            return Controller.api.formatter.status(data)
                        }
                    },
                    { "needControlShow":true, "title": "最后登录时间", "data":'logintime',
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.datetime(data)
                        }
                    },
                    { "needControlShow":false, "title": "操作", "data":'id', "orderable": false,
                        render:function(data, type, row, meta){
                            if(data == Config.admin.id){
                                return '';
                            }
                            return dataTable.api.formatter.operate('id',data)
                        }
                    }
                ],
                "showExport": true,
                "exportIgnoreColumn":[0,8],
                "showColumn": true,
                "columnCheckbox":true,
                "extend": {
                    index_url: 'auth/user/index',
                    add_url: 'auth/user/add',
                    edit_url: 'auth/user/edit',
                    del_url: 'auth/user/del'
                }
            });
            // console.log($.fn.dataTable.defaults)
            var table = $('#table');
            //初始化表格
            var tableObj = table.DataTable()

            //TODO:为表格绑定事件
            dataTable.api.bindevent(table, tableObj);
        },
        add: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        edit: function () {
            Form.api.bindevent($("form[role=form]"));
        },
        api: {
            formatter: {
                status: function(value, row, index) {
                    return value == 'normal' ? '<span class="text-success"><i class="fa fa-circle"></i> 正常</span>' : '<span class="text-grey"><i class="fa fa-circle"></i> 隐藏</span>';
                }
            }
        }
    };
    return Controller;
});
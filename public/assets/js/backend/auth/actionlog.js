define([
    'jquery', 'bootstrap', 'backend', 'datatable'
], function($, undefined, Backend, dataTable) {
    var Controller = {
        index: function() {
            dataTable.api.init({
                "aaSorting": [[ 6, "desc" ]],
                "columns": [
                    { "needControlShow":true, "title": "序号", "data":null,
                        render:function(data, type, row, meta){
                            // 显示行号
                            var startIndex = meta.settings._iDisplayStart;
                            return startIndex + meta.row + 1;
                        }
                    },
                    { "needControlShow":true, "title": "用户名", "data":'username'},
                    { "needControlShow":true, "title": "标题", "data":'title'},
                    { "needControlShow":true, "title": "Url", "data":'url',
                        render: function(data, type, row, meta){
                            return dataTable.api.formatter.url(data)
                        }
                    },
                    { "needControlShow":true, "title": "ip", "data":'ip'},
                    { "needControlShow":true, "title": "Browser", "data":'useragent',
                        render: function(data, type, row, meta) {
                            return Controller.api.formatter.browser(data)
                        }
                    },
                    { "needControlShow":true, "title": "操作时间", "data":'createtime'},
                    { "needControlShow":false, "title": "操作", "data":'id', "orderable": false,
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.operate('id',data)
                        }
                    }
                ],
                "showExport": true,
                "exportIgnoreColumn":[7],
                "showColumn": true,
                "columnCheckbox":false,
                "extend": {
                    index_url: '/admin/auth/actionlog/index',
                    detail_url : '/admin/auth/actionlog/detail'
                }
            });
            var table = $('#table');
            //初始化表格
            var tableObj = table.DataTable();

            //TODO:为表格绑定事件
            dataTable.api.bindevent(table, tableObj);
        },
        api: {
            formatter: {
                browser: function (value) {
                    return '<a class="btn btn-xs btn-browser">' + value.split(" ")[0] + '</a>';
                },
            },
        }
    }
    return Controller;
});
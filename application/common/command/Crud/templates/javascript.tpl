define(['jquery', 'bootstrap', 'backend', 'datatable', 'form'], function ($, undefined, Backend, dataTable, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            dataTable.api.init({
                "columns": [
                    {%javascriptList%}
                ],
                "showExport": true,
                "exportIgnoreColumn":[7],
                "showColumn": true,
                "columnCheckbox":true,
                "extend": {
                    index_url: '{%controllerUrl%}/index',
                    add_url: '{%controllerUrl%}/add',
                    edit_url: '{%controllerUrl%}/edit',
                    del_url: '{%controllerUrl%}/del'
                }
            });

            var table = $("#table");

            //初始化表格
            var tableObj = table.DataTable();

            //TODO:为表格绑定事件
            dataTable.api.bindevent(table, tableObj);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
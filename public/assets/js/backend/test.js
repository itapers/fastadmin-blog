define(['jquery', 'bootstrap', 'backend', 'datatable', 'form'], function ($, undefined, Backend, dataTable, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            dataTable.api.init({
                "columns": [
                    { 'needControlShow':true, data: 'id', title: '主键'},
                    { 'needControlShow':true, data: 'category_id', title: '分类ID'},
                    { 'needControlShow':true, data: 'category_ids', title: '分类集合'},
                    { 'needControlShow':true, data: 'title', title: '文章标题'},
                    { 'needControlShow':true, data: 'author', title: '作者'},
                    { 'needControlShow':true, data: 'desc', title: '简介'},
                    { 'needControlShow':true, data: 'pic', title: '配图',
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.image(data)
                        }
                    },
                    { 'needControlShow':true, data: 'week_text', title: '星期'},
                    { 'needControlShow':true, data: 'sexdata_text', title: '性别'},
                    { 'needControlShow':true, data: 'attrdata_text', title: '属性'},
                    { 'needControlShow':true, data: 'publishtime', title: '发布时间',
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.datetime(data)
                        }
                    },
                    { 'needControlShow':true, data: 'createtime', title: '创建时间',
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.datetime(data)
                        }
                    },
                    { 'needControlShow':true, data: 'updatetime', title: '更新时间',
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.datetime(data)
                        }
                    },
                    { "needControlShow":false, "title": "操作", "data":"id", "orderable": false, 
                        render:function(data, type, row, meta){
                            return dataTable.api.formatter.operate("id",data)
                        }
                    }
                ],
                "showExport": true,
                "exportIgnoreColumn":[7],
                "showColumn": true,
                "columnCheckbox":true,
                "extend": {
                    index_url: 'test/index',
                    add_url: 'test/add',
                    edit_url: 'test/edit',
                    del_url: 'test/del'
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
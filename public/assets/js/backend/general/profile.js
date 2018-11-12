define([
    'jquery', 'bootstrap', 'backend', 'datatable', 'form', 'upload'
], function($, undefined, Backend, dataTable, Form, Upload) {
    var Controller = {
        index: function() {
            dataTable.api.init({
                "columns": [
                    { "needControlShow":true, "title": "序号", "data":null,
                        render:function(data, type, row, meta){
                            // 显示行号  
                            var startIndex = meta.settings._iDisplayStart;  
                            return startIndex + meta.row + 1;  
                        }
                    },
                    { "needControlShow":true, "title": "标题", "data":'title'},
                    { "needControlShow":true, "title": "Url", "data":'url',
                        render: function(data, type, row, meta){
                            return dataTable.api.formatter.url(data)
                        }
                    },
                    { "needControlShow":true, "title": "ip", "data":'ip'},
                    { "needControlShow":true, "title": "操作时间", "data":'createtime'}
                ],
                "aaSorting": [[ 4, "desc" ]],
                "showExport": true,
                "showColumn": true,
                "columnCheckbox":false,
                "extend": {
                    index_url: '/admin/general/profile/index'
                }
            });
            var table = $('#table');
            //初始化表格
            var tableObj = table.DataTable()

            //TODO:为表格绑定事件
            dataTable.api.bindevent(table, tableObj);

            // 给上传按钮添加上传成功事件
            $("#plupload-avatar").data("upload-success", function (data) {
                var url = Backend.api.cdnurl(data.url);
                $(".profile-user-img").prop("src", url);
                Toastr.success("上传成功！");
            });
            
            // 给表单绑定事件
            Form.api.bindevent($("#update-form"), function () {
                $("input[name='row[password]']").val('');
                var url = $("#c-avatar").val();
                // top.window.$(".user-panel .image img,.user-menu > a > img,.user-header > img").prop("src", url);
                return true;
            });
        }
    }
    return Controller;
});
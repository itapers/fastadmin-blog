define([
    'jquery',
    'datatable'
], function($) {
    var dataTable = {
        defaults: {
            "columns": [
            ],
            "ajax": {
                url : '',
                data: {

                }
            },
            //开启分页
            "paging": true,
            //开启排序
            "ordering": true,
            //开启服务端模式
            "serverSide": false,
            //是否显示处理状态
            'processing':true,
            //状态保存 - 再次加载页面时还原表格状态,包含分页位置，每页显示的长度，过滤后的结果和排序
            "stateSave":true,
            "dom":'f<t>ilp',
            'searching':true,
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "所有"] ],
            //多语言配置
            "language": {},
            //默认排序列
            "aaSorting": [[ 1, "asc" ]],
            "drawCallback": function(){
                layer.closeAll();
                $(dataTable.config.refreshbtn).find('i').removeClass('fa-spin')
            },
            //当处理大数据时，延迟渲染数据，有效提高Datatables处理能力
            "deferRender": false,
            "initComplete":function(row, data, start, end, display){
                // console.log($.fn.dataTable.defaults)
                var settings = $.fn.dataTable.defaults;
                var selector = this.selector;
                var html = '<div class="columns columns-right btn-group pull-right">';
                console.log(settings.columns)
                //TODO:是否增加列显示隐藏按钮
                if(settings.showColumn){
                    html += '<div id="hide-show-btn" class="keep-open btn-group" title="列"><button type="button" aria-label="columns" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="glyphicon glyphicon-th icon-th"></i> <span class="caret"></span></button><ul style="left:-96%" class="dropdown-menu" role="menu">';
                    columns = settings.columns;
                    for(var j in columns){
                        if(columns[j].needControlShow){
                            // console.log(columns[j]);
                            html += '<li role="menuitem"><label><input id="show-hide-'+columns[j].title+'" type="checkbox" data-field="'+columns[j].data+'" value="'+j+'" class="showColumn"> '+columns[j].title+'</label></li>';
                        }
                    }
                    html += '</ul></div>';

                }
                //TODO:是否增加前端导出按钮
                if(settings.showExport){
                    html += '<div id="export-btn" class="export btn-group"><button class="btn btn-default dropdown-toggle" aria-label="export type" title="导出数据" data-toggle="dropdown" type="button"><i class="glyphicon glyphicon-export icon-share"></i> <span class="caret"></span></button><ul class="dropdown-menu" style="left:-194%" role="menu"><li role="menuitem" data-type="json"><a href="javascript:void(0)">JSON</a></li><li role="menuitem" data-type="xml"><a href="javascript:void(0)">XML</a></li><li role="menuitem" data-type="csv"><a href="javascript:void(0)">CSV</a></li><li role="menuitem" data-type="txt"><a href="javascript:void(0)">TXT</a></li><li role="menuitem" data-type="doc"><a href="javascript:void(0)">MS-Word</a></li><li role="menuitem" data-type="excel"><a href="javascript:void(0)">MS-Excel</a></li></ul></div>';
                }
                html += '</div>';
                $('#toolbar').append(html)
                //TODO:调整输入框的样式问题
                $(selector + '_filter').css('float','right');
                $(selector + '_filter').css('margin-right','5px');
                $(selector + '_filter input').addClass('form-control')
                $('#toolbar').append($(selector + '_filter'));
                if(settings.showColumn){
                    //初始化，按钮是否勾选
                    $(selector + ' th').each(function(){
                        var text = $(this).text();
                        $('#show-hide-'+text).attr('checked','true');
                    })
                }


            },
            showExport:false,   //是否显示导出按钮
            exportIgnoreColumn:[],//导出忽略列，从0开始
            showColumn:false,   //是否显示列显示隐藏
            columnCheckbox:false,//是否默认自带行多选框
            checkboxVal:'id',//设置多选框的取值
            extend: {
                index_url: '',
                add_url: '',
                edit_url: '',
                del_url: '',
                detail_url: '',
                import_url: '',
            }
        },
        //TODO:配置语言包
        oLanguage:{
            "processing": "正在加载中......",
            "loadingRecords": "正在加载中......",
            "lengthMenu": "显示 _MENU_ 条记录",
            "zeroRecords": "抱歉，没有找到",
            "emptyTable": "查询无数据",
            "info": "当前显示 _START_ 到 _END_ 条，共 _TOTAL_ 条记录",
            "infoEmpty": "",
            "infoFiltered": "",
            "search": "",
            "searchPlaceholder":"请输入查询内容",
            "paginate": {
                "first": "首页",
                "previous": "上一页",
                "next": "下一页",
                "last": "末页"
            }
        },
        //TODO:定义操作按钮
        config: {
            firsttd: 'tbody tr td:first-child:not(:has(div.card-views))',
            toolbar: '.toolbar',
            refreshbtn: '.btn-refresh',
            addbtn: '.btn-add',
            editbtn: '.btn-edit',
            delbtn: '.btn-del',
            importbtn: '.btn-import',
            multibtn: '.btn-multi',
            disabledbtn: '.btn-disabled',
            editonebtn: '.btn-editone',        },
        api: {
            init: function(defaults, oLanguage){
                layer.load(1, {
                    shade: [0.1, '#fff'] //0.1透明度的白色背景
                });
                defaults = defaults ? defaults : {};
                console.log(defaults)
                if(defaults.columnCheckbox){
                    var array = new Array();
                    var checkboxVal = defaults.checkboxVal!=undefined && defaults.checkboxVal!=''?defaults.checkboxVal:dataTable.defaults.checkboxVal;
                    console.log(checkboxVal)
                    array.push({"needControlShow":false,"title": "<input type='checkbox' id='all' />", "data":checkboxVal, "targets":0,"sClass":"bs-checkbox", "orderable": false,
                        render: function(data, type, row, meta){
                            console.log('值：',data)
                            return "<input type='checkbox' name='ids[]' class='rowCheckbox' value='"+data+"' />"
                        }
                    })
                    for(i in defaults.columns){
                        array.push(defaults.columns[i])
                    }
                    defaults.columns = array;
                }

                oLanguage = oLanguage ? oLanguage : {};
                // 写入datatable默认配置
                $.extend(true, $.fn.dataTable.defaults, dataTable.defaults, defaults);
                // 写入datatable language
                $.extend($.fn.dataTable.defaults.oLanguage, dataTable.oLanguage, oLanguage);
                // $(tableNameId).DataTable(defaults)
            },
            //刷新table
            refreshTable: function(tableObj,params){
                tableObj.settings()[0].ajax.data = params;
                tableObj.ajax.reload();
            },
            // 绑定事件
            bindevent: function (table, tableObj) {
                var options = $.fn.dataTable.defaults;
                // 刷新按钮事件
                $('.toolbar').on('click', dataTable.config.refreshbtn, function () {
                    console.log('refresh')
                    layer.load(1, {
                        shade: [0.1, '#fff'] //0.1透明度的白色背景
                    });
                    $(this).children('i').addClass('fa-spin')
                    tableObj.ajax.reload();
                });
                // 添加按钮事件
                $('.toolbar').on('click', dataTable.config.addbtn, function () {
                    // var ids = dataTable.api.selectedids(table);
                    // console.log(this)
                    Hippo.api.open(options.extend.add_url , '添加', $(this).data() || {});
                });
                //单个编辑
                $(table).on('click','.btn-editone', function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    Hippo.api.open($(this).attr('href'), '编辑', $(this).data() || {});
                })
                //单个删除
                $(table).on("click", ".btn-delone", function (e) {
                    e.preventDefault();
                    var that = $(this);
                    var index = Layer.confirm(
                            '确定删除此项?',
                            {icon: 3, title: '温馨提示', shadeClose: true},
                            function () {
                                var options = {url: that.attr('href')};
                                Hippo.api.ajax(options, function (data) {
                                    tableObj.ajax.reload();
                                });
                                Layer.close(index);
                            }
                    );
                });
                // 导入按钮事件
                if ($(dataTable.config.importbtn).size() > 0) {
                    require(['upload'], function (Upload) {
                        Upload.api.plupload($(dataTable.config.importbtn), function (data, ret) {
                            Hippo.api.ajax({
                                url: options.extend.import_url,
                                data: {file: data.url},
                            }, function () {
                                dataTable.api.refreshTable(tableObj,{});
                            });
                        });
                    });
                }
                // // 批量编辑按钮事件
                $('#toolbar').on('click', dataTable.config.editbtn, function () {
                    var ids = $('.showColumn:checked');
                    var that = this;
                    //循环弹出多个编辑框
                    $('.rowCheckbox:checked').each(function(){
                        Hippo.api.open(options.extend.edit_url + (options.extend.edit_url.match(/(\?|&)+/) ? "&ids=" : "/ids/") + $(this).val(), '编辑', $(that).data() || {});
                    })
                });
                // 批量删除按钮事件
                $('#toolbar').on('click', dataTable.config.delbtn, function () {
                    var that = this;
                    var index = Layer.confirm(
                            '确定删除选中的 ' + $('.rowCheckbox:checked').length + ' 项?',
                            {icon: 3, title: '温馨提示', offset: 0, shadeClose: true},
                            function () {
                                //循环弹出多个编辑框
                                var arr = new Array();

                                $('.rowCheckbox:checked').each(function(){
                                    arr[i] = $(this).val();
                                })
                                var vals = arr.join(",");
                                var params = {url: options.extend.del_url, data: {ids: vals}};
                                Hippo.api.ajax(params, function (data) {
                                    tableObj.ajax.reload();
                                });
                                Layer.close(index);
                            }
                    );
                });
                //TODO:控制行是否隐藏
                $("#toolbar").on("click", '#hide-show-btn .dropdown-menu li', function (e) {
                    e.stopPropagation();
                })
                $('#toolbar').on('change', '.showColumn', function (e) {
                    // e.preventDefault();
                    var obj = $(this);
                    if (obj.attr('checked')){
                        obj.removeAttr('checked')
                    }
                    var column = tableObj.column( obj.val() );
                    column.visible( ! column.visible() );
                });

                //TODO:勾选事件
                //checkbox全选
                $(table).on("click", "#all", function () {
                    if ($(this).prop("checked") === true) {
                        $(".rowCheckbox").prop("checked", true);
                        $(table).find('tbody tr').addClass('selected');
                        $('#toolbar').find(dataTable.config.editbtn + ',' + dataTable.config.delbtn).removeClass('disabled');
                    } else {
                        $(".rowCheckbox").prop("checked", false);
                        $(table).find('tbody tr').removeClass('selected');
                        $('#toolbar').find(dataTable.config.editbtn + ',' + dataTable.config.delbtn).addClass('disabled');
                    }
                });
                //单选
                $(table).on('click', '.rowCheckbox', function () {
                    var $tr = $(this).parents('tr');
                    $tr.toggleClass('selected');
                    var $tmp = $('.rowCheckbox');
                    $('#all').prop('checked', $tmp.length == $tmp.filter(':checked').length);
                    if($(this).prop('checked')){
                        $('#toolbar').find(dataTable.config.editbtn + ',' + dataTable.config.delbtn).removeClass('disabled');
                    } else if($('.rowCheckbox:checked').length == 0) {
                        $('#toolbar').find(dataTable.config.editbtn + ',' + dataTable.config.delbtn).addClass('disabled');
                    }

                });

                //导出
                $('#toolbar').on('click','#export-btn .dropdown-menu li',function(){
                    console.log($(table).find('tr.selected').length)
                    //判断是否有选中，如无选中，则导出全部
                    var selected_length = $(table).find('tr.selected').length
                    if(selected_length>0){
                        // console.log($(table).find('tr.selected').clone())
                        if($('#exportTemp').length>0){
                            $('#exportTemp').remove();
                        }
                        $('body').append('<table id="exportTemp"></table>')
                        $('#exportTemp').append($(table).find('tr:eq(0)').clone())
                        $('#exportTemp').append($(table).find('tr.selected').clone())
                        $('#exportTemp').tableExport({type:$(this).attr('data-type'),ignoreColumn:options.exportIgnoreColumn});
                        $('#exportTemp').remove();
                    } else {
                        $('#table_length').find('select').val('-1').change();
                        $(table).tableExport({type:$(this).attr('data-type'),ignoreColumn:options.exportIgnoreColumn});
                        $('#table_length').find('select').val('10').change();
                    }
                })
                return table;
            },

            // 单元格数据格式化
            formatter: {
                icon: function (value, row, index) {
                    if (!value)
                        return '';
                    value = value.indexOf(" ") > -1 ? value : "fa fa-" + value;
                    //渲染fontawesome图标
                    return '<i class="' + value + '"></i> ' + value;
                },
                image: function (value, row, index) {
                    value = value ? value : '/assets/img/blank.gif';
                    var classname = typeof this.classname !== 'undefined' ? this.classname : 'img-sm img-center';
                    return '<img class="' + classname + '" src="' + Hippo.api.cdnurl(value) + '" />';
                },
                images: function (value, row, index) {
                    value = value.toString();
                    var classname = typeof this.classname !== 'undefined' ? this.classname : 'img-sm img-center';
                    var arr = value.toString().split(',');
                    var html = [];
                    $.each(arr, function (i, value) {
                        value = value ? value : '/assets/img/blank.gif';
                        html.push('<img class="' + classname + '" src="' + Hippo.api.cdnurl(value) + '" />');
                    });
                    return html.join(' ');
                },
                status: function (value, row, index) {
                    //颜色状态数组,可使用red/yellow/aqua/blue/navy/teal/olive/lime/fuchsia/purple/maroon
                    var colorArr = {normal: 'success', hidden: 'grey', deleted: 'danger', locked: 'info'};
                    //如果字段列有定义custom
                    if (typeof this.custom !== 'undefined') {
                        colorArr = $.extend(colorArr, this.custom);
                    }
                    value = value.toString();
                    var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                    value = value.charAt(0).toUpperCase() + value.slice(1);
                    //渲染状态
                    var html = '<span class="text-' + color + '"><i class="fa fa-circle"></i> ' + value + '</span>';
                    return html;
                },
                url: function (value, row, index) {
                    return '<div class="input-group input-group-sm" style="width:250px;"><input type="text" class="form-control input-sm" value="' + value + '"><span class="input-group-btn input-group-sm"><a href="' + value + '" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a></span></div>';
                },
                search: function (value, row, index) {
                    return '<a href="javascript:;" class="searchit" data-field="' + this.field + '" data-value="' + value + '">' + value + '</a>';
                },
                addtabs: function (value, row, index) {
                    var url = Table.api.replaceurl(this.url, value, row, this.table);
                    var title = this.atitle ? this.atitle : ("搜索 " + value);
                    return '<a href="' + Hippo.api.fixurl(url) + '" class="addtabsit" data-value="' + value + '" title="' + title + '">' + value + '</a>';
                },
                dialog: function (value, row, index) {
                    var url = Table.api.replaceurl(this.url, value, row, this.table);
                    var title = this.atitle ? this.atitle : ('查看 ' + value);
                    return '<a href="' + Hippo.api.fixurl(url) + '" class="dialogit" data-value="' + value + '" title="' + title + '">' + value + '</a>';
                },
                flag: function (value, row, index) {
                    var colorArr = {index: 'success', hot: 'warning', recommend: 'danger', 'new': 'info'};
                    //如果字段列有定义custom
                    if (typeof this.custom !== 'undefined') {
                        colorArr = $.extend(colorArr, this.custom);
                    }
                    if (typeof this.customField !== 'undefined' && typeof row[this.customField] !== 'undefined') {
                        value = row[this.customField];
                    }
                    //渲染Flag
                    var html = [];
                    var arr = value.toString().split(',');
                    $.each(arr, function (i, value) {
                        value = value.toString();
                        if (value == '')
                            return true;
                        var color = value && typeof colorArr[value] !== 'undefined' ? colorArr[value] : 'primary';
                        value = value.charAt(0).toUpperCase() + value.slice(1);
                        html.push('<span class="label label-' + color + '">' + value + '</span>');
                    });
                    return html.join(' ');
                },
                //时间格式化
                datetime: function (value, row, index) {
                    console.log(typeof(value))
                    if(typeof(value) == 'number'){
                        value = value.toString();
                    }
                    var regNeg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/; //时间格式
                    if (!value) { return ''; }
                    if(value.indexOf('-')>-1 || value.indexOf(':')>-1){
                        return value;
                    } else {
                        return value ? Moment(parseInt(value) * 1000).format("YYYY-MM-DD HH:mm:ss") : '无';
                    }

                },
                operate: function (column,value) {
                    // 操作配置
                    var options = $.fn.dataTable.defaults? $.fn.dataTable.defaults: {};
                    // 默认按钮组
                    var buttons = [];
                    //查看按钮
                    buttons.push({name: 'detail', 'text': '详情', icon: 'fa fa-list', classname: 'btn btn-primary btn-xs btn-detail btn-dialog', url: options.extend.detail_url});
                    //编辑按钮
                    buttons.push({name: 'edit', icon: 'fa fa-pencil', classname: 'btn btn-xs btn-success btn-editone', url: options.extend.edit_url});
                    //删除按钮
                    buttons.push({name: 'del', icon: 'fa fa-trash', classname: 'btn btn-xs btn-danger btn-delone', url: options.extend.del_url});

                    var html = [];
                    var url, classname, icon, text, title, extend;
                    $.each(buttons, function (i, j) {
                        if (['add', 'edit', 'del', 'detail'].indexOf(j.name) > -1 && !options.extend[j.name + "_url"]) {
                            return true;
                        }
                        // console.log('循环按钮');
                        var attr = $('#table').data("operate-" + j.name);
                        if (typeof attr === 'undefined' || attr) {
                            url = j.url ? j.url : '';
                            url = url ? Hippo.api.fixurl(url+'/'+column+'s/'+value) : 'javascript:;';
                            classname = j.classname ? j.classname : 'btn-primary btn-' + name + 'one';
                            icon = j.icon ? j.icon : '';
                            text = j.text ? j.text : '';
                            title = j.title ? j.title : text;
                            extend = j.extend ? j.extend : '';console.log()
                            html.push('<a href="' + url + '" class="' + classname + '" ' + extend + ' title="' + title + '"><i class="' + icon + '"></i>' + (text ? ' ' + text : '') + '</a>');
                        }
                    });
                    return html.join(' ');
                },
            },
        }
    }
    return dataTable;
});
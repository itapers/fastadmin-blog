<div class="panel panel-default panel-intro">
    {:build_heading()}

    <div class="panel-body">
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade active in" id="one">
                <div class="widget-body no-padding">
                    <div id="toolbar" class="toolbar">
                        <a href="javascript:;" class="btn btn-primary btn-refresh" title="刷新" ><i class="fa fa-refresh"></i> </a>
                        <a href="javascript:;" class="btn btn-success btn-add {:$auth->check('{%controllerUrl%}/add')?'':'hide'}" title="添加" ><i class="fa fa-plus"></i> 添加</a>
                        <a href="javascript:;" class="btn btn-success btn-edit btn-disabled disabled {:$auth->check('{%controllerUrl%}/edit')?'':'hide'}" title="编辑" ><i class="fa fa-pencil"></i> 编辑</a>
                        <a href="javascript:;" class="btn btn-danger btn-del btn-disabled disabled {:$auth->check('{%controllerUrl%}/del')?'':'hide'}" title="删除" ><i class="fa fa-trash"></i> 删除</a>
                        <a href="javascript:;" class="btn btn-danger btn-import {:$auth->check('{%controllerUrl%}/import')?'':'hide'}" title="导入" id="btn-import-file" data-url="ajax/upload" data-mimetype="csv,xls,xlsx" data-multiple="false"><i class="fa fa-upload"></i> 导入</a>
                    </div>
                    <table id="table" class="table table-striped table-bordered table-hover"
                           data-operate-edit="{:$auth->check('{%controllerUrl%}/edit')}"
                           data-operate-del="{:$auth->check('{%controllerUrl%}/del')}"
                           width="100%">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

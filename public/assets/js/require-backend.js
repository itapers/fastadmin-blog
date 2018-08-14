require.config({
    urlArgs: "v=" + requirejs.s.contexts._.config.config.site.version,
    packages: [{
        name: 'moment',
        location: '../libs/moment',
        main: 'moment'
    }],
    include: ['css', 'layer', 'toastr', 'hippo', 'backend', 'form', 'dragsort', 'datatable', 'selectpage'],
    //插件路径
    paths: {
        'form': 'require-form',
        'datatable': 'require-data-table',
        'upload': 'require-upload',
        'validator': 'require-validator',
        'echartsobj': 'require-echarts',
        'echarts': 'echarts.min',
        'adminlte': 'adminlte',
        //=============地图json数据================
        'china': './map/china',
        'beijing': './map/beijing',
        'sichuan': './map/sichuan',
        'chengdu': './map/chengdu',
        'chongqing': './map/chongqing',
        'fujian': './map/fujian',
        'gansu': './map/gansu',
        'guangdong': './map/guangdong',
        'guangxi': './map/guangxi',
        'henan': './map/henan',
        'hubei': './map/hubei',
        'hunan': './map/hunan',
        'neimenggu': './map/neimenggu',
        'ningxia': './map/ningxia',
        'qinghai': './map/qinghai',
        'shanxi': './map/shanxi',
        'xinjiang': './map/xinjiang',
        'yunnan': './map/yunnan',
        //=============地图json数据================
        'chosen': '../libs/chosen/chosen.jquery.min',
        'jquery': '../libs/jquery/dist/jquery.min',
        'jquery-multiselect': '../libs/jquery/bootstrap-multiselect',
        'bootstrap': '../libs/bootstrap/dist/js/bootstrap.min',
        'bootstrap-select': '../libs/bootstrap-select/dist/js/bootstrap-select.min',
        'bootstrap-select-lang': '../libs/bootstrap-select/dist/js/i18n/defaults-zh_CN',
        'bootstrap-datetimepicker': '../libs/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min',
        'bootstrap-daterangepicker': '../libs/bootstrap-daterangepicker/daterangepicker',
        'datatables.net': '../libs/datatables/media/js/jquery.dataTables.min',
        'dragsort': '../libs/dragsort/jquery.dragsort',
        'tableexport': '../libs/tableExport.jquery.plugin/tableExport.min',
        'summernote': '../libs/summernote/dist/lang/summernote-zh-CN.min',
        'plupload': '../libs/plupload/js/plupload.min',
        'template': '../libs/art-template/dist/template-native',
        'validator-core': '../libs/nice-validator/dist/jquery.validator',
        'validator-lang': '../libs/nice-validator/dist/local/zh-CN',
        'toastr': '../libs/toastr/toastr',
        'jstree': '../libs/jstree/dist/jstree.min',
        'addtabs': '../libs/jquery-addtabs/jquery.addtabs',
        'layer': '../libs/layer/src/layer',
        'slimscroll': '../libs/jquery-slimscroll/jquery.slimscroll',
        'selectpage': '../libs/fastadmin-selectpage/selectpage',
        'cxselect': '../libs/jquery-cxSelect/js/jquery.cxselect',
        'async': 'async',
        'BMap': ['http://api.map.baidu.com/api?v=2.0&ak=mXijumfojHnAaN2VxpBGoqHM'],
    },
    //插件依赖
    shim: {
        'bootstrap': ['jquery'],
        //         'bootstrap-table': {
        //             deps: [
        //                 'bootstrap',
        // //                'css!../libs/bootstrap-table/dist/bootstrap-table.min.css'
        //             ],
        //             exports: '$.fn.bootstrapTable'
        //         },

        'bootstrap-datetimepicker': [
            'moment/locale/zh-cn',
            //            'css!../libs/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
        ],
        'bootstrap-select': ['css!../libs/bootstrap-select/dist/css/bootstrap-select.min.css', ],
        'bootstrap-select-lang': ['bootstrap-select'],
        'tableexport': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'summernote': ['../libs/summernote/dist/summernote.min', 'css!../libs/summernote/dist/summernote.css'],
        'jstree': ['css!../libs/jstree/dist/themes/default/style.css', ],
        'plupload': {
            deps: ['../libs/plupload/js/moxie.min'],
            exports: "plupload"
        },
        'validator-lang': ['validator-core'],
        'slimscroll': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'adminlte': {
            deps: ['bootstrap', 'slimscroll'],
            exports: '$.AdminLTE'
        },
        'datatable': {
            deps: ['datatables.net', 'tableexport'],
            exports: '$.fn.dataTable'
        },
        'BMap': {
            deps: ['jquery'],
            exports: 'BMap'
        }
    },
    baseUrl: '/assets/js/',
    map: {
        '*': {
            'css': '../libs/require-css/css.min'
        }
    },
    waitSeconds: 30,
    charset: 'utf-8' // 文件编码
})

require(['jquery', 'bootstrap'], function($, undefined) {
    //初始配置
    var Config = requirejs.s.contexts._.config.config;
    //将Config渲染到全局
    window.Config = Config;
    console.log(Config)
    var paths = {};
    // 避免目录冲突
    paths['backend/'] = 'backend/';
    require.config({
        paths: paths
    });
    console.log(paths)
    //初始化
    $(function() {
        require(['hippo'], function(Hippo) {
            require(['backend'], function(Backend) {
                //加载相应模块
                console.log(Config)
                console.log(Config.jsname)
                if (Config.jsname) {
                    require([Config.jsname], function(Controller) {
                        console.log(Controller)
                        console.log(Controller[Config.actionname])
                        // console.log(Controller[Config.actionname] != undefined && Controller[Config.actionname]())
                        Controller[Config.actionname] != undefined && Controller[Config.actionname]();
                    }, function(e) {
                        console.error(e);
                        // 这里可捕获模块加载的错误
                    });
                }
            });
        })
    })

})
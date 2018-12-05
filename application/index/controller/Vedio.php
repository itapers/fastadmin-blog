<?php

namespace app\index\controller;

use think\Controller;

class Vedio extends Controller
{
    public function initialize()
    {
        parent::initialize();
        $this->model = model('admin/Article');
        //查询分类
        $all = model('admin/Category')->select()->toArray();
        $this->assign('category', $all);

        //采集程序
        $param = $_SERVER["QUERY_STRING"];
        $collect = new CollectVideo();
        $data = $collect->getVideoData($param);
        $this->assign('data', $data[0]);
    }

    //视频地址解析
    public function index()
    {
        $url = input('get.url');
        $this->assign('url', $url);
        return $this->fetch();
    }

    //视频分类页面
    public function vedio()
    {
        return $this->fetch();
    }


}

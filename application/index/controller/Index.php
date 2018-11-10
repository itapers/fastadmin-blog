<?php

namespace app\index\controller;

use think\Controller;
use wind\tree;
use QL\QueryList;
class Index extends Controller
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ['index'];

    public function initialize()
    {
        parent::initialize();
        $this->model = model('admin/Article');
        $this->view->assign("attrdataList", $this->model->getAttrdataList());
        $all = model('admin/Category')->order("ord desc,id desc")->select()->toArray();
        $tree = Tree::instance()->init($all, 'pid');
        $channelOptions = $tree->getTree(0, "<option value='@id' @selected>@spacer@name</option>", '');
        $this->assign('channelOptions', $channelOptions);
    }

    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $page = input('post.page');
            $limit = input('post.limit');
            $list = $this->model
                ->with(['category'])
                ->order('createtime desc')
                ->limit($page,$limit)
                ->select();
            $result = ['data' => $list];
            return json($result);
        }
        return $this->fetch();
    }

    //详情
    public function details()
    {
        $id = input('get.id');
        $data = $this->model->find($id);
        $this->assign('data',$data);
        return $this->fetch();
    }

}

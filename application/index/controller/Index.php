<?php

namespace app\index\controller;

use think\Controller;

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
        //查询分类
        $all = model('admin/Category')->select()->toArray();
        $this->assign('category', $all);
    }

    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $category_id = input('post.cid',1) ? input('post.cid',1) : 1;
            $page = input('post.page');
            $limit = input('post.limit');
            $list = $this->model
                ->where('category_id',$category_id)
                ->with(['category'])
                ->order('createtime desc')
                ->limit($page, $limit)
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
        $this->assign('data', $data);
        //浏览量加1
        $this->model->where('id', $id)->setInc('views');
        return $this->fetch();
    }

    /**
     * 判断文件名是否有效，.slf结尾的文件名就是有效的，返回真
     * @param string $filename
     */
    public function isValidLogFileName($filename)
    {
        if (!preg_match('/\.SLF$/i', $filename)){
            return false;
        }
        return true;
    }

    public function vedio()
    {

    }


}

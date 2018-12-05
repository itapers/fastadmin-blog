<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use wind\tree;

/**
 * 文章管理
 *
 */
class Article extends Backend
{

    /**
     * Article模型对象
     * @var \app\admin\model\Article
     */
    protected $model = null;

    public function initialize()
    {
        parent::initialize();
        $this->model = model('Article');
        $this->view->assign("attrdataList", $this->model->getAttrdataList());
        $all = model('Category')->order("ord desc,id desc")->select()->toArray();
        $tree = Tree::instance()->init($all, 'pid');
        $channelOptions = $tree->getTree(0, "<option value='@id' @selected>@spacer@name</option>", '');
        $this->assign('channelOptions', $channelOptions);
    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $rr = input('get.');
            $rows['where'] = $rr['search']['value'];
            $where[] = ['title|name','like', '%' . $rows['where'] . '%'];
            //过滤超级管理员
            if (session('admin.id') > 1) {
                //非超级管理员只能看到自己发布的文章
                $where[] = ['from','=',session('admin.id')];
            }
            $list = $this->model
                ->where($where)
                ->with(['category'])
                ->order('createtime','desc')
                ->limit($rr['start'], $rr['length'])
                ->select();
            $count = $this->model
                ->where($where)
                ->with(['category'])
                ->count();
            $result = ['data' => $list, 'recordsTotal' => $count, 'recordsFiltered' => $count];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     *
     */
    public function edit()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error('请选择要编辑的记录！');
        }
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error('记录未找到');
        }

        if ($this->request->isPost()) {
            //获取提交信息，转换数组
            $params = $this->request->post('row/a');
            if ($params) {
                $row->save($params);
                $this->success();
            }
        }
        $all = model('Category')->order("ord desc,id desc")->select()->toArray();
        $tree = Tree::instance()->init($all, 'pid');
        $channelOptions = $tree->getTree(0, "<option value='@id' @selected>@spacer@name</option>", $row['category_id']);
        $this->assign('channelOptions', $channelOptions);
        $this->view->assign('row', $row);

        return $this->fetch();
    }

}

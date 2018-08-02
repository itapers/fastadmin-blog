<?php

/**
 * @Author: hippo
 * @Date:   2018-06-11 15:44:43
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-07-10 16:34:59
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use wind\Tree;

class Category extends Backend
{
    protected $model = null;
    protected $categorylist = [];
    protected $noNeedRight = ['selectpage'];

    public function initialize()
    {
        parent::initialize();
        $this->request->filter(['strip_tags']);
        $this->model = model('Category');
        $categoryList = $this->model->orderRaw('if(ord=0,99999,ord)')->select()->toArray();
        Tree::instance()->init($categoryList);
        $this->categoryList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $catedata[] = '无';
        foreach ($this->categoryList as $k => &$v) {
            $catedata[$v['id']] = $v['name'];
        }
        $this->view->assign('catedata', $catedata);
    }

    /**
     * 列表.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $list = $this->categoryList;
            $result = array('data' => $list);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 添加.
     */
    public function add()
    {
        if ($this->request->isPost()) {
            //获取提交信息，转换数组
            $params = $this->request->post('row/a', [], 'strip_tags');
            if ($params) {
                $this->model->create($params);
                $this->success();
            }
        }

        return $this->fetch();
    }

    /**
     * 编辑.
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
            $params = $this->request->post('row/a', [], 'strip_tags');
            if ($params) {
                $row->save($params);
                $this->success();
            }
        }
        $this->view->assign('row', $row);

        return $this->fetch('add');
    }

    /**
     * 删除.
     */
    public function del()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error('请选择要删除的记录！');
        }
        $delIds = [];
        foreach (explode(',', $ids) as $k => $v) {
            $delIds = array_merge($delIds, Tree::instance()->getChildrenIds($v, true));
        }
        $delIds = array_unique($delIds);
        $count = $this->model->where('id', 'in', $delIds)->delete();
        if ($count) {
            $this->success();
        }
    }

    /**
     * Selectpage搜索
     *
     * @internal
     */
    public function selectpage()
    {
        return parent::selectpage();
    }
}

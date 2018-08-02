<?php
/*
 * @Author: hippo
 * @Date: 2018-01-22 08:23:05
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-04-16 13:02:44
 */

namespace app\admin\controller\auth;

use app\admin\model\AuthRule;
use app\common\controller\Backend;
use Cache;
use wind\Tree;

class Rule extends Backend
{
    public function initialize()
    {
        parent::initialize();
        $this->model = model('AuthRule');
        $ruleList = $this->model->orderRaw('if(weigh=0,99999,weigh)')->select()->toArray();
        Tree::instance()->init($ruleList);
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'title');
        $ruledata[] = '无';
        foreach ($this->rulelist as $k => &$v) {
            if ($v['status'] == 'hidden') {
                continue;
            }
            $ruledata[$v['id']] = $v['title'];
        }
        $this->view->assign('ruledata', $ruledata);
    }

    /**
     * 列表.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $list = $this->rulelist;
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
                Cache::rm('__menu__');
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
                Cache::rm('__menu__');
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
            Cache::rm('__menu__');
            $this->success();
        }
    }
}

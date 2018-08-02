<?php

/**
 * @Author: hippo
 * @Date:   2018-06-08 10:02:57
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-06-08 10:04:12
 */
namespace app\admin\library\traits;

trait Backend
{
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model

                ->where($where)
                ->order($sort, $order)
            // ->limit($offset, $limit)
                ->select();
            return json(['data' => $list]);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     */
    public function add()
    {
        if ($this->request->isPost()) {
            //获取提交信息，转换数组
            $params = $this->request->post('row/a');
            if ($params) {
                $this->model->create($params);
                $this->success();
            }
        }
        return $this->fetch();
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
        $this->view->assign('row', $row);

        return $this->fetch();
    }

    /**
     * 删除
     *
     */
    public function del()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error('请选择要删除的记录！');
        }
        $delIds = [];
        if (is_array($ids)) {
            $delIds = $ids;
        } else {
            $delIds = explode(',', $ids);
        }
        $delIds = array_unique($delIds);
        $count = $this->model->where('id', 'in', $delIds)->delete();
        if ($count) {
            $this->success();
        }
    }
}

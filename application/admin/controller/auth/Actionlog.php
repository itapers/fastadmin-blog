<?php
/*
 * @Author: hippo
 * @Date: 2018-02-05 13:41:09
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-08 11:09:02
 */

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\common\controller\Backend;

class Actionlog extends Backend
{
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];
    //无需要权限判断的方法
    protected $noNeedRight = ['detail'];

    public function initialize()
    {
        parent::initialize();
        $this->model = model('Actionlog');

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin() ? true : false);

        $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
            ->column('id,name');

        $this->view->assign('groupdata', $groupName);
    }

    /**
     * 查看.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->where($where)
                ->where('uid', 'in', $this->childrenAdminIds)
                ->order($sort, $order)
                ->select();
            $result = array('data' => $list);

            return json($result);
        }

        return $this->fetch();
    }

    /*
     * 查看详情
     */

    public function detail()
    {
        $ids = input('ids');
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error('记录不存在！');
        }
        $this->view->assign('row', $row->toArray());

        return $this->fetch();
    }
}

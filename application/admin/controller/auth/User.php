<?php
/*
 * @Author: hippo
 * @Date: 2018-01-19 10:28:55
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-31 18:30:58
 */

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use wind\Random;
use wind\Tree;

class User extends Backend
{
    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];
    protected $searchFields = 'username';
    public function initialize()
    {
        parent::initialize();
        $this->model = model('User');

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(true);

        $groupList = AuthGroup::where('id', 'in', $this->childrenGroupIds)->select()->toArray();
        $groupIds = $this->auth->getGroupIds();
        Tree::instance()->init($groupList);
        $result = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
        } else {
            foreach ($groupIds as $m => $n) {
                $result = array_merge($result, Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n)));
            }
        }
        $groupName = [];
        foreach ($result as $k => $v) {
            $groupName[$v['id']] = $v['name'];
        }

        $this->view->assign('groupdata', $groupName);
        $this->assignconfig('admin', ['id' => $this->auth->id]);
    }

    /**
     * 用户管理.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            //TODO:获取当前用户拥有的权限组：权限名称以及ID
            $groupName = AuthGroup::where('id', 'in', $this->childrenGroupIds)
                ->column('id,name');
            $authGroupList = AuthGroupAccess::where('group_id', 'in', $this->childrenGroupIds)
                ->field('uid,group_id')
                ->select();

            $adminGroupName = [];
            foreach ($authGroupList as $k => $v) {
                if (isset($groupName[$v['group_id']])) {
                    $adminGroupName[$v['uid']][$v['group_id']] = $groupName[$v['group_id']];
                }

            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model
                ->where($where)
                ->where('id', 'in', $this->childrenAdminIds)
                ->field(['password', 'salt', 'token'], true)
                ->order($sort, $order)
                ->select();
            foreach ($list as $k => &$v) {
                $groups = isset($adminGroupName[$v['id']]) ? $adminGroupName[$v['id']] : [];
                $v['groups'] = implode(',', array_keys($groups));
                $v['groups_text'] = implode(',', array_values($groups));
            }
            unset($v);
            $result = array("data" => $list);

            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * 用户添加.
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $group = $this->request->post("group/a");
            if (!$group) {
                $this->error('角色不能为空');
            }
            if (!$params['username']) {
                $this->error('用户名不能为空');
            }
            if (!$params['password']) {
                $this->error('密码不能为空');
            }
            $params['salt'] = Random::alnum();
            $params['password'] = md5(md5($params['password']) . $params['salt']);
            $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。

            $admin = $this->model->create($params);

            //过滤不允许的组别,避免越权
            $group = array_intersect($this->childrenGroupIds, $group);
            $dataset = [];
            foreach ($group as $value) {
                $dataset[] = ['uid' => $admin->id, 'group_id' => $value];
            }
            model('AuthGroupAccess')->saveAll($dataset);
            $this->success();
        }
        return $this->view->fetch();
    }

    /**
     * 用户编辑.
     */
    public function edit()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error('请选择要编辑的记录');
        }
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error('记录未找到');
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $group = $this->request->post("group/a");
            if (!$group) {
                $this->error('角色不能为空');
            }
            if (!$params['username']) {
                $this->error('用户名不能为空');
            }
            $params['salt'] = Random::alnum();
            if ($params['password']) {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
            } else {
                unset($params['password'], $params['salt']);
            }
            $params['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。

            $row->save($params);

            // 先移除所有权限
            model('AuthGroupAccess')->where('uid', $row->id)->delete();

            //过滤不允许的组别,避免越权
            $group = array_intersect($this->childrenGroupIds, $group);
            $dataset = [];
            foreach ($group as $value) {
                $dataset[] = ['uid' => $row->id, 'group_id' => $value];
            }
            model('AuthGroupAccess')->saveAll($dataset);
            $this->success();
        }
        $grouplist = $this->auth->getGroups($row['id']);
        $groupids = [];
        foreach ($grouplist as $k => $v) {
            $groupids[] = $v['id'];
        }
        $this->view->assign("row", $row);
        $this->view->assign("groupids", $groupids);
        return $this->view->fetch('add');
    }

    /**
     * 用户删除.
     */
    public function del()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error();
        }
        // 避免越权删除管理员
        $childrenGroupIds = $this->childrenGroupIds;
        $adminList = $this->model->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
            $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
        })->select();
        if ($adminList) {
            $deleteIds = [];
            foreach ($adminList as $k => $v) {
                $deleteIds[] = $v->id;
            }
            $deleteIds = array_diff($deleteIds, [$this->auth->id]);
            if ($deleteIds) {
                $this->model->destroy($deleteIds);
                model('AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                $this->success();
            }
        }
        $this->error();
    }
}

<?php
/*
 * @Author: hippo
 * @Date: 2018-01-23 15:26:18
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-07-19 16:09:47
 */

namespace app\admin\controller\auth;

use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\common\controller\Backend;
use wind\Tree;

class Group extends Backend
{
    protected $model = null;
    //当前登录管理员所有子组别
    protected $childrenGroupIds = [];
    //当前组别列表数据
    protected $groupdata = [];
    //无需要权限判断的方法
    protected $noNeedRight = [];

    public function initialize()
    {
        parent::initialize();
        $this->model = model('AuthGroup');
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds(true);

        $groupList = AuthGroup::where('id', 'in', $this->childrenGroupIds)->select()->toArray();
        $groups = $this->auth->getGroups();
        Tree::instance()->init($groupList);
        $result = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
        } else {
            foreach ($groups as $m => $n) {
                $result = array_merge($result, Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n['pid'])));
            }
        }
        $groupName = [];
        foreach ($result as $k => $v) {
            $groupName[$v['id']] = $v['name'];
        }

        $this->groupdata = $groupName;
        $this->assignconfig('admin', ['id' => $this->auth->id, 'group_ids' => $this->auth->getGroupIds()]);

        $this->view->assign('groupdata', $this->groupdata);
    }

    /**
     * 角色管理列表.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $list = AuthGroup::all(array_keys($this->groupdata))->toArray();
            $groupList = [];
            foreach ($list as $k => $v) {
                $groupList[$v['id']] = $v;
            }
            $list = [];
            foreach ($this->groupdata as $k => $v) {
                if (isset($groupList[$k])) {
                    $groupList[$k]['name'] = $v;
                    $list[] = $groupList[$k];
                }
            }
            $result = array('data' => $list);

            return json($result);
        }

        return $this->fetch();
    }

    /**
     * 角色添加.
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a", [], 'strip_tags');
            $params['rules'] = explode(',', $params['rules']);
            if (!in_array($params['pid'], $this->childrenGroupIds)) {
                $this->error('父组别不能是自身的子组别');
            }
            $parentmodel = model("AuthGroup")->get($params['pid']);
            if (!$parentmodel) {
                $this->error('父组别未找到');
            }
            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);
            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();
            $rules = $params['rules'];
            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);
            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);
            $params['rules'] = implode(',', $rules);
            if ($params) {
                $this->model->create($params);
                $this->success();
            }
            $this->error();
        }
        return $this->fetch();
    }

    /**
     * 角色编辑.
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
            $params = $this->request->post("row/a", [], 'strip_tags');
            $params['rules'] = explode(',', $params['rules']);
            if (!in_array($params['pid'], $this->childrenGroupIds)) {
                $this->error('父组别不能是自身的子组别');
            }
            $parentmodel = model("AuthGroup")->get($params['pid']);
            if (!$parentmodel) {
                $this->error('父组别未找到');
            }
            // 父级别的规则节点
            $parentrules = explode(',', $parentmodel->rules);
            // 当前组别的规则节点
            $currentrules = $this->auth->getRuleIds();
            $rules = $params['rules'];
            // 如果父组不是超级管理员则需要过滤规则节点,不能超过父组别的权限
            $rules = in_array('*', $parentrules) ? $rules : array_intersect($parentrules, $rules);
            // 如果当前组别不是超级管理员则需要过滤规则节点,不能超当前组别的权限
            $rules = in_array('*', $currentrules) ? $rules : array_intersect($currentrules, $rules);
            $params['rules'] = implode(',', $rules);
            if ($params) {
                $row->save($params);
                $this->success();
            }
            $this->error();
        }
        $this->assign('row', $row);
        return $this->fetch('add');
    }

    /**
     * 角色删除.
     */
    public function del()
    {
        $ids = input('ids');
        if (!$ids) {
            $this->error();
        }
        $ids = explode(',', $ids);
        //collect转数组，array_map需要传入数组
        $grouplist = $this->auth->getGroups()->toArray();
        $group_ids = array_map(function ($group) {
            return $group['id'];
        }, $grouplist);
        // 移除掉当前管理员所在组别
        $ids = array_diff($ids, $group_ids);

        // 循环判断每一个组别是否可删除
        $grouplist = $this->model->where('id', 'in', $ids)->select();
        $groupaccessmodel = model('AuthGroupAccess');
        foreach ($grouplist as $k => $v) {
            // 当前组别下有管理员
            $groupone = $groupaccessmodel->get(['group_id' => $v['id']]);
            if ($groupone) {
                $ids = array_diff($ids, [$v['id']]);
                continue;
            }
            // 当前组别下有子组别
            $groupone = $this->model->get(['pid' => $v['id']]);
            if ($groupone) {
                $ids = array_diff($ids, [$v['id']]);
                continue;
            }
        }
        if (!$ids) {
            $this->error('你不能删除含有子组和管理员的组');
        }
        $count = $this->model->where('id', 'in', $ids)->delete();
        if ($count) {
            $this->success();
        }
    }

    /**
     * 读取角色权限树
     *
     * @internal
     */
    public function roletree()
    {
        $model = model('AuthGroup');
        $id = $this->request->post("id");
        $pid = $this->request->post("pid");
        $parentgroupmodel = $model->get($pid);
        $currentgroupmodel = null;
        if ($id) {
            $currentgroupmodel = $model->get($id);
        }
        if (($pid || $parentgroupmodel) && (!$id || $currentgroupmodel)) {
            $id = $id ? $id : null;
            $ruleList = model('AuthRule')->orderRaw('if(weigh=0,99999,weigh)')->select()->toArray();
            //读取父类角色所有节点列表
            $parentRuleList = [];
            if (in_array('*', explode(',', $parentgroupmodel->rules))) {
                $parentRuleList = $ruleList;
            } else {
                $parent_rule_ids = explode(',', $parentgroupmodel->rules);
                foreach ($ruleList as $k => $v) {
                    if (in_array($v['id'], $parent_rule_ids)) {
                        $parentRuleList[] = $v;
                    }
                }
            }

            //当前所有正常规则列表
            Tree::instance()->init($ruleList);

            //读取当前角色下规则ID集合
            $admin_rule_ids = $this->auth->getRuleIds();
            //是否是超级管理员
            $superadmin = $this->auth->isSuperAdmin();
            //当前拥有的规则ID集合
            $current_rule_ids = $id ? explode(',', $currentgroupmodel->rules) : [];

            if (!$id || !in_array($pid, Tree::instance()->getChildrenIds($id, true))) {
                $ruleList = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
                $hasChildrens = [];
                foreach ($ruleList as $k => $v) {
                    if ($v['haschild']) {
                        $hasChildrens[] = $v['id'];
                    }

                }
                $nodelist = [];
                foreach ($parentRuleList as $k => $v) {
                    if (!$superadmin && !in_array($v['id'], $admin_rule_ids)) {
                        continue;
                    }

                    $state = array('selected' => in_array($v['id'], $current_rule_ids) && !in_array($v['id'], $hasChildrens));
                    $nodelist[] = array('id' => $v['id'], 'parent' => $v['pid'] ? $v['pid'] : '#', 'text' => $v['title'], 'type' => 'menu', 'state' => $state);
                }
                $this->success('', null, $nodelist);
            } else {
                $this->error('父组别不能是它的子组别!');
            }
        } else {
            $this->error('组别未找到');
        }
    }
}

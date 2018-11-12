<?php
/*
 * @Author: hippo
 * @Date: 2018-01-20 14:39:13
 * @Last Modified by: hippo
 * @Last Modified time: 2018-02-07 11:53:21
 */

namespace app\admin\controller\general;

use app\common\controller\Backend;
use app\admin\model\Actionlog;
use Session;
use wind\Random;

class Profile extends Backend
{
    //个人配置页面
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $model = model('Actionlog');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $model
                    ->where($where)
                    ->where('uid', $this->auth->id)
                    ->order($sort, $order)
                    ->select();

            $result = array('data' => $list);

            return json($result);
        }

        return $this->view->fetch();
    }

    //个人配置-修改密码保存
    public function update()
    {
        if ($this->request->isPost()) {
            //强制转换为数组类型参数
            $params = $this->request->post('row/a');
            $params = array_filter(array_intersect_key($params, array_flip(array('email', 'nickname', 'password', 'avatar'))));
            if (isset($params['password'])) {
                $params['salt'] = Random::alnum();
                $params['password'] = md5(md5($params['password']).$params['salt']);
            }
            if ($params) {
                model('user')->where('id', $this->auth->id)->update($params);
                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                $admin = Session::get('admin');
                $admin_id = $admin ? $admin->id : 0;
                if ($this->auth->id == $admin_id) {
                    $admin = model('user')->get(['id' => $admin_id]);
                    Session::set('admin', $admin);
                }
                $this->success();
            }
            $this->error();
        }
    }
}

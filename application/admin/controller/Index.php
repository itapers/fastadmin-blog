<?php
/*
 * @Author: hippo
 * @Date: 2018-02-06 21:02:53
 * @Last Modified by:   hippo
 * @Last Modified time: 2018-05-04 10:21:26
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use Cache;

class Index extends Backend
{
    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    // 后台首页
    public function index()
    {
        $menulist = $this->auth->getSidebar();
        $this->assign('menulist', $menulist);
        return $this->fetch();
    }

    // 登录
    public function login()
    {
        //判断是否登录，已登录的话，直接进入系统
        if ($this->auth->isLogin()) {
            $this->success('已经登录，无需重复登录！', 'index/index');
        }
        $request = $this->request;
        if ($request->isPost()) {
            //TODO:获取用户名与密码
            $username = $request->post('username');
            $password = $request->post('password');
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username' => 'require|length:3,30',
                'password' => 'require|length:3,30',
                '__token__' => 'token',
            ];
            $data = [
                'username' => $username,
                'password' => $password,
                '__token__' => $token,
            ];
            if (config('mySet.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new \think\Validate($rule, [], ['username' => '用户名', 'password' => '密码', 'captcha' => '验证码']);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            \app\admin\model\Actionlog::setTitle('登录');
            $result = $this->auth->login($username, $password, $keeplogin ? 86400 : 0);
            if (true === $result) {
                $url = 'index/index';
                $this->success('登陆成功！', $url, ['url' => $url]);
            } else {
                $this->error('用户名或者密码错误！');
            }
        }

        return $this->fetch();
    }

    // 退出系统
    public function logout()
    {
        $this->auth->logout();
        Cache::clear();
        $this->success('退出登录成功！', 'index/login');
    }
}

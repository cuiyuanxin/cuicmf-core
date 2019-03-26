<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Login模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\Common;
use think\facade\Config;
use think\facade\Session;

class Login extends Common {
    
    /**
     * 后台登录
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    public function login() {
        //已登录
        if (is_login()) {
            $this->redirect('Index/index');
        }
        if ($this->request->isAjax()) {
            //获取post数据
            $data = $this->request->post('', [], 'trim');
            // 验证数据
            $result = $this->validate($data, 'app\admin\validate\User.login');
            if (true !== $result) {
                $this->error($result);
            }
            $model = app()->model('User');
            $user = $model::where(['username' => $data['username']])->field('id, username, nickname, realname, password, status')->find();
            $password = cui_ucenter_md5($data['password'], Config::get('cui_config.uc_auth_key'));
            if (empty($user)) {
                $this->error('username:用户名不存在！');
            } elseif ($user['password'] != $password) {
                $this->error('password:密码错误！');
            } elseif (0 == $user['status']) {
                $this->error('username:帐号被禁用！');
            } elseif (2 == $user['status']) {
                $this->error('username:帐号锁定中！');
            } elseif (3 == $user['status']) {
                $this->error('username:帐号审核中！');
            } else {
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip' => ipToint($this->request->ip())
                ];
                $auth = array(
                    'uid' => think_encrypt($user['id'], Config::get('cui_config.uc_auth_key')),
                    'username' => think_encrypt($user['username'], Config::get('cui_config.uc_auth_key')),
                    'realname' => think_encrypt($user['realname'], Config::get('cui_config.uc_auth_key')),
                    'nickname' => think_encrypt($user['nickname'], Config::get('cui_config.uc_auth_key')),
                    'last_login_time' => think_encrypt($data['last_login_time'], Config::get('cui_config.uc_auth_key'))
                );
                if ($model::where(['id' => $user['id']])->update($data)) {
                    Session::set('user_auth', $auth);
                    //记录行为
    //                action_log('user_login', 'user', $user['id'], $user['id']);
                    $this->success('登录成功！', url('Index/index'));
                } else {
                    $this->error('登录失败！');
                }
            }
        } else {
            $this->assign('meta_title', '登录');
            return $this->fetch();
        }
    }

    /**
     * 退出
     * @author 崔元欣 <15811506097@163.com>
     */
    public function logout() {
        Session::delete('user_auth');
        $this->success('退出成功，前往登录页面', url('Login/login'));
    }

    /**
     * 验证码
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    public function verify() {
        ob_end_clean();
        $verify = new \think\captcha\Captcha([
            'fontSize' => 28,
            'imageH' => 76,
            'imageW' => 0,
            'length' => 6,
            'useCurve' => true
        ]);
        return $verify->entry(1);
    }
    
}
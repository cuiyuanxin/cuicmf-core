<?php

// +----------------------------------------------------------------------
// | cuicmf User验证类
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\validate;

use think\Validate;

class User extends Validate {
    
    protected $rule =   [
        'group_id' => 'require|checkLength',
        'username' => 'require|max:20|alphaDash|unique:user',
        'password' => 'require|max:25|alphaNum',
        'confirm_password' => 'require|max:25|alphaNum|confirm:password',    
        'realname' => 'max:10',
        'nickname' => 'max:10',
        'mobile' => 'require|mobile|unique:user',
        'email' => 'require|email|unique:user',
        'status' => 'require',
        'verify' => 'require|checkVerify'
    ];
    
    protected $message = [
        'group_id.require' => 'group_id:用户组必须填写!',
        'username.require' => 'username:用户名必须填写!',
        'username.max' => 'username:用户名最多20个字符!',
        'username.alphaDash' => 'username:用户名只允许字母和数字，下划线_及破折号-!',
        'username.unique' => 'username:用户名已存在!',
        'password.require' => 'password:用户密码必须填写!',
        'password.max' => 'password:用户密码最长25个字符!',
        'password.alphaNum' => 'password:用户密码只允许字母和数字!',
        'confirm_password.require' => 'confirm_password:用户密码必须填写!',
        'confirm_password.max' => 'confirm_password:用户密码最长25个字符!',
        'confirm_password.alphaNum' => 'confirm_password:用户密码只允许字母和数字!',
        'confirm_password.confirm' => 'confirm_password:用户密码两次输入不一致!',
        'realname.max' => 'realname:真实姓名不能大于10个字符!',
        'nickname.max' => 'nickname:昵称不能大于10个字符!',
        'mobile.require' => 'mobile:手机号必须填写!',
        'mobile.mobile' => 'mobile:手机号格式不正确!',
        'mobile.unique' => 'mobile:手机号已存在!',
        'email.require' => 'email:邮箱必须填写!',
        'email.email' => 'email:邮箱格式不正确!',
        'email.unique' => 'email:邮箱已存在!',
        'status.require' => 'status:用户组状态必须选择!',
        'verify.require' => 'verify:请输入验证码！'
    ];
    
    /**
     * create 验证场景定义
     * @return type
     */
    public function sceneCreate() {
    	return $this->only(['group_id', 'username', 'password', 'confirm_password', 'realname', 'nickname', 'mobile', 'email', 'status']);
    } 
    
    /**
     * update 验证场景定义
     * @return type
     */
    public function sceneUpdate() {
    	return $this->only(['group_id', 'realname', 'nickname', 'mobile', 'email', 'status']);
    } 
    
    /**
     * login 验证场景定义
     * @return type
     */
    public function sceneLogin() {
    	return $this->only(['username', 'password', 'verify'])->remove('username', 'unique')->append('username', 'checkOnlyLogin');
    } 
    
    /**
     * 验证用户组个数
     * @param type $value
     * @param type $rule
     * @param type $data
     * @return boolean|string
     */
    public function checkLength($value, $rule, $data) {
        $length = explode(',', $value);
        if(isset($length) && count($length) > 3) {
            return 'group_id:用户组最多选择3个!！';
        }
        return true;
    }
    
    /**
     * 验证验证码是否正确
     * @param type $value
     * @param type $rule
     * @param type $data
     * @return boolean|string
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function checkVerify($value, $rule, $data) {
        if (check_verify($value, 1) === true) {
            return true;
        }
        return 'verify:验证码错误！';
    }
    
    /**
     * 验证用户名是否存在
     * @param type $value
     * @param type $rule
     * @param type $data
     * @return boolean|string
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function checkOnlyLogin($value, $rule, $data) {
        if (!$this->unique($value, 'user', $data, 'username')) {
            return true;
        }
        return 'username:用户名不存在，请联系管理员！';
    }
}

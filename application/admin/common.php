<?php

// +----------------------------------------------------------------------
// | cuicmf 后台公共方法文件
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

// 后台应用公共文件

use think\facade\Config;

/**
 * 获取缓存登录信息
 * @param type $str
 */
function user_auth($str = '') {
    $user_auth = Session::get('user_auth');
    if ($str && isset($user_auth[$str])) {
        return think_decrypt($user_auth[$str], Config::get('cui_config.uc_auth_key'));
    } else {
        return '';
    }
    return $user_auth;
}

/**
 * 获取当前角色组
 * @param type $id
 */
function group_rule($find = 'title') {
    if(UID == Config::get('cui_config.user_administrator')) {
        return '系统管理员';
    } else {
        $model = app()->model('AuthGroupAccess');
        $group_id = $model::where(['uid' => 2])->column('group_id');
        $group_title = $model->group()->where(['id' => implode(',', $group_id)])->column($find);
        return implode(',', $group_title);
    }
}

/**
 * 遍历文件夹并删除下边的文件
 * @param type $dirname 目录地址
 * @param type $path 目录地址
 * @return boolean
 */
function rm_dir($dirname = '', $path = '') {
    if (!file_exists($dirname)) {
        return false;
    }
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
    $dir = dir($dirname);
    if ($dir) {
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            rm_dir($dirname . DIRECTORY_SEPARATOR . $entry);
        }
    }
    $dir->close();
    return @rmdir($dirname);
}

/**
 * 递归创建某目录
 * @param unknown $dirname 目录地址
 * @return boolean
 */
function mk_dir($dirname){
    if (!is_dir($dirname)) {
        if (!mk_dir(dirname($dirname))) {
            return false;
        }
        if (!mkdir($dirname, 0777)) {
            return false;
        }
    }
    return true;
}
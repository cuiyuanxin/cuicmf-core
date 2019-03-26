<?php

// +----------------------------------------------------------------------
// | cuicmf 公共方法文件
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\facade\Hook;
use think\facade\Config;
use think\facade\Cache;
use think\facade\Session;

/**
 * 获取根目录
 * @return type
 */
function root() {
    $root = $phpfile = '';
    $iscgi = (0 === strpos(PHP_SAPI, 'cgi') || false !== strpos(PHP_SAPI, 'fcgi')) ? 1 : 0;
    if ($iscgi) {
        //CGI/FASTCGI模式下
        $temp = explode('.php', Request::root());
        $phpfile = rtrim(str_replace($_SERVER['HTTP_HOST'], '', $temp[0] . '.php'), '/');
    } else {
        $phpfile = rtrim($_SERVER['SCRIPT_NAME'], '/');
    }
    $root = rtrim(dirname($phpfile), '/');
    return (($root == '/' || $root == '\\' || $root == '.') ? '' : $root);
}

/**
 * 动态替换cdn地址
 * @param type $path 路径
 * @param type $suffix 后缀
 * @param type $extends 额外参数
 * @return type
 */
function resources($path, $suffix = '', $extends = [] ) {
    $extend = ['type' => 0, 'min' => true];
    $extend = array_merge($extend, $extends);
    if($path){
        $cdn = Config::get('cui_config.object_storage', '');
        $pathArray = explode('/', $path);
        $paths = '';
        $ext = ['png', 'jpg', 'jpeg', 'gif', 'ico'];
        if(isset($extend['type']) && $extend['type'] === 0){
            if($suffix){
                $version = Config::get('cui_config.site_version', time());
                if (Config::get('app.app_debug') === false) {
                    $min = isset($extend['min']) && $extend['min'] === true ? 'min.' : '';
                    $paths = in_array($suffix, $ext) ? '.' . $suffix . '?v=' . $version : '.' . $min . $suffix . '?v=' . $version;
                } else {
                    $paths = '.' . $suffix . '?v=' . $version;
                }
            }
            if(isset($cdn) && $cdn){
                $cdnPath = '';
                if(isset($pathArray[1]) && $pathArray[1] == 'static' && isset($pathArray[2]) && $pathArray[2] !== 'static'){
                    $cdnPath = $cdn;
                    return $suffix ? $cdnPath . $paths : $cdnPath;
                } else if (isset($pathArray[2]) && $pathArray[2] == 'static') {
                    $cdnPath = preg_replace('/\/static/', $cdn . '/common', $path, 1);
                    return $suffix ? $cdnPath . $paths : $cdnPath;
                } else {
                    $cdnPath = preg_replace('/\/static/', $cdn, $path, 1);
                    return $suffix ? $cdnPath . $paths : $cdnPath;
                }
            }
            return $path . $paths;
        } else {
            if(count($pathArray) == 2 && isset($pathArray[1]) && $pathArray[1] == 'static'){
                return isset($cdn) && $cdn ? $cdn . '/' : $path . '/';
            } else if (count($pathArray) > 2 && isset($pathArray[1]) && $pathArray[1] == 'static' && isset($pathArray[2]) && $pathArray[2] == 'static') {
                return preg_replace('/\/static\//', '', $path, 1) . '/';
            } else {
                return preg_replace('/\/static\//', '', $path, 1) . '/';
            }
        }
    }
    return $path;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 单位 秒
 * @return string
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key = empty($key) ? 'Cuicmf' : $key;
    $key = md5($key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 */
function think_decrypt($data, $key = '') {
    $key = empty($key) ? 'Cuicmf' : $key;
    $key = md5($key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l)
            $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 把IP转换成整型
 * @param type $ip ip
 * @return type
 */
function ipToint($ip){
    $iparr = explode('.',$ip);
    $num = 0;
    for($i=0;$i<count($iparr);$i++){
        $num += intval($iparr[$i]) * pow(256,count($iparr)-($i+1));
    }
    return $num;
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 * @author 崔元欣 <15811506097@163.com>
 */
function cui_ucenter_md5($str, $key = '') {
    $key = empty($key) ? 'CuiCMF' : $key;
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 验证码是否正确
 * @param type $code 验证码
 * @param type $id ID
 * @return type
 */
function check_verify($code, $id = '') {
    $captcha = new \think\captcha\Captcha();
    return $captcha->check($code, $id);
}

/**
 * 检测用户是否登录
 * @param type $session 保存登录信息的session名称
 * @param type $field 查找保存的字段数据
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login($session = 'user_auth', $field = 'uid') {
    $user = Session::get($session . '.' . $field);
    if (empty($user)) {
        return 0;
    } else {
        return think_decrypt($user, Config::get('cui_config.uc_auth_key'));
    }
}
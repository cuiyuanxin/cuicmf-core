<?php

// +----------------------------------------------------------------------
// | cuicmf 模板替换
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019F All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

use think\facade\Request;

return [
    'tpl_replace_string' => [
        '__ROOT__' => root() . "/",
        '__PUBLIC__' => root() . '/static',
        '__STATIC__' => root() . '/static/static',
        '__ADDONS__' => root() . '/static/' . Request::module() . '/addons',
        '__IMG__' => root() . '/static/' . Request::module() . '/images',
        '__CSS__' => root() . '/static/' . Request::module() . '/css',
        '__JS__' => root() . '/static/' . Request::module() . '/js',
        '__STATICS__' => root() . '/static/' . Request::module() . '/static'
    ]
];
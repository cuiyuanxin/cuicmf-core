<?php

// +----------------------------------------------------------------------
// | cuicmf Menu验证类
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\validate;

use think\Validate;

class Menu extends Validate {
    
    protected $rule =   [
        'pid' => 'require',
        'title' => 'require|max:10|unique:auth_rule',
        'url' => 'require',
        'status' => 'require'
    ];
    
    protected $message = [
        'pid.require' => 'pid:父级菜单必须选择!',
        'title.require' => 'title:菜单名称必须填写!',
        'title.max' => 'title:菜单名称最多10个字符!',
        'title.unique' => 'title:菜单名称已存在!',
        'url.require' => 'url:菜单链接必须填写!',
        'status.require' => 'status:菜单状态必须选择!'
    ];
    
}

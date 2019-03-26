<?php

// +----------------------------------------------------------------------
// | cuicmf Hooks验证类
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\validate;

use think\Validate;

class Hooks extends Validate {
    
    protected $rule =   [
        'name' => 'require|max:40|unique:hooks',
        'status' => 'require',    
    ];
    
    protected $message = [
        'name.require' => 'name:钩子名称必须填写!',
        'name.max' => 'name:用户组名称最多40个字符!',
        'name.unique' => 'name:用户组已存在!',
        'status.require' => 'status:钩子状态必须选择!'
    ];
    
}

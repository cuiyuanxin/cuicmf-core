<?php

// +----------------------------------------------------------------------
// | cuicmf Auth验证类
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\validate;

use think\Validate;

class Auth extends Validate {
    
    protected $rule =   [
        'title' => 'require|max:15|unique:auth_group',
        'description' => 'max:50',
        'status' => 'require',    
    ];
    
    protected $message = [
        'title.require' => 'title:用户组名称必须填写!',
        'title.max' => 'title:用户组名称最多15个字符!',
        'title.unique' => 'title:用户组已存在!',
        'description.max' => 'description:用户组描述最多50个字符!',
        'status.require' => 'status:用户组状态必须选择!'
    ];
    
    /**
     * group 验证场景定义
     * @return type
     */
    public function sceneGroup() {
    	return $this->only(['title', 'description', 'status']);
    } 
}

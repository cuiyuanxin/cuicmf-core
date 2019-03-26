<?php

// +----------------------------------------------------------------------
// | cuicmf 初始化
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019F All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\common\behavior;

use think\facade\Request;
use think\facade\Config;

class Init {
    
    /**
     * 初始化
     */
    public function run() {
        // 如果api模块则跳出自定义行为
//        if (strtolower($module) == 'api') {
//            return;
//        }

        // debug时关闭模板编译缓存
//        if (Env::get('app_debug')) {
//            Config::set('template.tpl_cache', false);
//        }
    }
}



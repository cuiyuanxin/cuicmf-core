<?php

// +----------------------------------------------------------------------
// | cuicmf 后台System模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\facade\Env;

class System extends Admin{
    
    /**
     * 清空缓存
     */
    public function clear() {
        //临时文件
        $runtime = Env::get('runtime_path');
        $path = ['cache', 'log', 'temp'];
        $ret = shell_exec('ls');
        if (file_exists($runtime)) {
            foreach ($path as $key => $value) {
                rm_dir($runtime . $value, $path);
            }
            $this->success('缓存清楚成功！');
        } else {
            $this->error('无法找到缓存目录！');
        }
    }
    
}
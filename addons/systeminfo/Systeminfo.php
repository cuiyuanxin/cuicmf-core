<?php

// +----------------------------------------------------------------------
// | cuicmf 系统环境信息插件模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace addons\systeminfo;

use think\Db;
use think\Addons;

/**
 * 系统环境信息插件
 * @author byron sampson
 */
class Systeminfo extends Addons 
{
    public $info = [
        'name' => 'systeminfo',
        'title' => '系统环境信息',
        'description' => '用于显示一些服务器的信息',
        'status' => 1,
        'author' => 'cuiyuanxin',
        'version' => '0.1'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install() {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall() {
        return true;
    }

    /**
     * 实现的 adminIndex 钩子方法
     * @param $param 参数
     * @return mixed
     * @throws \Exception
     */
    public function adminIndex($param) {
        $config = $this->getConfig();
        $this->assign('addons_config', $config);
        $this->assign('system_info_mysql', Db::query("select version() as version;"));
        if ($config['display']) {
            return $this->fetch('widget');
        }
    }

}
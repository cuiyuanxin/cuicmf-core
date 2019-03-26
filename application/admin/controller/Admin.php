<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Admin模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\Common;
use think\facade\Response;
use think\exception\HttpResponseException;
use tree\Tree;
use think\facade\Session;
use think\facade\Config;
use think\Auth;
use think\facade\Cache;

class Admin extends Common {
    
    // 忽略的规则,无需规则验证
    private $ignore = [
        'System/clear'
    ];
    
    //受保护的初始化方法
    protected function initialize() {
        parent::initialize();
        //未登陆，不允许直接访问
        if (!is_login()) {
            $this->redirect('Login/login');
        } else {
            define('UID', is_login());
        }
        
        // 初始化权限类
        $auth = Auth::instance();

        // 检查访问权限
        if (UID != Config::get('cui_config.user_administrator')) {
            $url = $this->request->controller() . '/' . $this->request->action();
            
            // 不需要检测的控制器/方法
            $not_check = $this->ignore;
            
            if(!in_array($url, $not_check)){
                if(!$auth->check($url, UID, ['in', '1,2'])) {
                    throw new \think\exception\HttpException(403, '提示:无权访问,您可能需要联系管理员为您授权!');
                }
            }
        }
        
        // 获取当前URL所属规则ID
        $id_curr = $this->get_url_id();
        // 获取父级规则数组
        $menus_curr = $this->get_admin_parents($id_curr);
        // 获取缓存菜单列表是否存在
        $menus = Cache::get('menu_list' . UID);
        if(empty($menus)) {
            $model = app()->model('Menu');
            // 查询规则列表
            $auth_rule = $model::where(['status' => 1])->field('id, pid, title, url, icon, level')->order('sort')->select()->toArray();
            // 判断当前登录UID不等于系统管理员
            if (UID != Config::get('cui_config.user_administrator')) {
                // 循环判断权限规则
                foreach ($auth_rule as $key => $value) {
                    if (!$auth->check($value['url'], UID)) {
                        unset($auth_rule[$key]);
                    }
                }
            }
            // 格式化规则列表
            $tree = new Tree($auth_rule);
            $menus = $tree->authtree('_child', '', false);
            // 缓存规则菜单
            Cache::tag('menu')->set('menu_list' . UID, $menus);
        }
        // 格式化规则为静态HTML代码
        $this->assign('menus', $this->gatMenuHtml($menus, $id_curr, $menus_curr));
    }
    
    /**
     * 获取指定url的id(可能为显示状态或非显示状态)
     * @param type $url 当前操作的URL
     * @return int 当前操作的url规则ID
     */
    final protected function get_url_id($url = '') {
        $url = $url ?: $this->request->controller() . '/' . $this->request->action();
//        $route = Request::route();
//        $query = preg_replace('/^.+\?/U', '', $value['url']);
//        if ($query !== $url) {
//            $myArray = [];
//            parse_str($query, $myArray);
//            array_intersect_assoc($route, $myArray);
//        }
//        if (isset($myArray)) {
//            $url .= '?' . http_build_query($myArray);
//        }
//            
//        if ($url == '//') {
//            $routeInfo = request()->routeInfo();
//            //插件管理
//            if ($routeInfo['route'] == '\think\addons\Base@execute') {
//                $menu_id = self::where('name', 'admin/Addons/addons_list')->order('level desc,sort')->value('id');
//                return $menu_id ?: 0;
//            }
//        }

        $model= app()->model('Menu');
        $menu_id = $model::where(['url' => $url, 'status' => 1])->order('level desc, sort')->value('id', 0); //4级或3级(如果4级,status是0,不显示)
        return $menu_id;
    }

    /**
     * 获取所有父节点id(含自身)
     * @param int $id 节点id
     * @return array 节点数组
     */
    final protected function get_admin_parents($id = 0) {
        $id = $id ?: $this->get_url_id();
        if (empty($id))
            return [];
        $model= app()->model('Menu');
        $lists = $model->order('level desc, sort')->column('pid', 'id');
        $ids = [];
        while (isset($lists[$id]) && $lists[$id] != 0) {
            $ids[] = $id;
            $id = $lists[$id];
        }
        if (isset($lists[$id]) && $lists[$id] == 0)
            $ids[] = $id;

        return array_reverse($ids);
    }
    
    /**
     * 格式化菜单html代码
     * @param type $menus 菜单规则数组
     * @param type $id_curr 当前操作规则ID
     * @param type $menus_curr 父级节点数组
     * @return string 菜单html代码
     */
    private function gatMenuHtml($menus = [], $id_curr, $menus_curr) {
        if(is_array($menus) && count($menus) > 0) {
            $html = '';
            foreach ($menus as $key => $value) {
                $html .= '<li class="';
                $html .= isset($value['_child']) ? 'treeview' : '';
                if(count($menus_curr) >= $value['level'] && $menus_curr[$value['level'] - 1] == $value['id']){
                    $html .= ' active';
                }
                $html .= '">';
                $html .= '<a href="' . url($value['url']) . '">';
                $html .= '<i class="';
                $html .= $value['icon'] ? $value['icon'] : 'fa fa-circle-o';
                $html .= '"></i> <span>' . $value['title'] . '</span>';
                if (isset($value['_child'])) {
                    $html .= '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>';
                }
                $html .= '</a>';

                if (isset($value['_child'])) {
                    $html .= '<ul class="treeview-menu">';
                    $html .= $this->gatMenuHtml($value['_child'], $id_curr, $menus_curr);
                    $html .= '</ul>';
                }

                $html .= '</li>';
            }
            return $html;
        }
        return '';
    }
    
    /**
    * 返回封装后的API数据到客户端(自定义)
    * @access protected
    * @param  mixed     $data 要返回的数据
    * @param  integer   $code 返回的code
    * @param  mixed     $msg 提示信息
    * @param  string    $type 返回数据格式
    * @param  array     $extend 自定义参数
    * @param  array     $header 发送的Header信息
    * @return void
    */
    protected function outcome($data, $code = 0, $msg = '', $type = '', array $extend = [], array $header = []) {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => time(),
            'data' => $data,
        ];

        if (is_array($extend) && count($extend) > 0) {
            $result = array_merge($result, $extend);
        }
        $type = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }
    
    /**
     * 格式化树形数据
     * @param type $model
     * @param type $map
     * @return type
     */
    protected function tree($model, $map = [], $pid = 0) {
        $menu = $model::where('level', 'between', '1,3')->field('id, pid, title as name')->select()->toArray();
        $tree = new Tree($menu);
        $html = $tree->getTree(0, "<option value='\$id' \$selected>\$prefix\$name</option>", $pid);
        return $html;
    }
    
}
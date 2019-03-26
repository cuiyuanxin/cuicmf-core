<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Addons模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

//use think\Db;
use think\Container;
use think\facade\Env;
use think\facade\Config;

class Addons extends Admin {
    
    /**
     * 插件管理
     * @return type
     */
    public function index() {
        if ($this->request->isAjax()) {
            $keywords = $this->request->post('key', '');
            $page = $this->request->post('page', 1);
            $limit = $this->request->post('limit', Config::get('cui_config.admin_list_rows'));
            $field = $this->request->post('field', 'id');
            $order = $this->request->post('order', 'desc');
            $model = app()->model('Addons');
            $query = $model->field('id, name, title, version, author, is_index, is_admin, setting, create_time, status');
            if (!empty($keywords['keywords'])) {
                $query->where('name|title', 'like', $keywords['keywords']);
            }
            $list = $query->order($field, $order)->page($page, $limit)->select();
            $list->append(['setting_length', 'status_text', 'create_text']);
            $count = $query->count();
            $this->outcome($list, 1, '查询成功', 'json', ['count' => $count], []);
        }
        $this->assign('meta_title', '插件列表');
        return $this->fetch();
    }
    
    /**
     * 未安装插件列表
     * @return mixed
     */
    public function uninstalled() {
        if ($this->request->isAjax()) {
            $keywords = $this->request->post('key', '');
            $page = $this->request->post('page', 1);
            $limit = $this->request->post('limit', Config::get('cui_config.admin_list_rows'));
            $field = $this->request->post('field', 'id');
            $order = $this->request->post('order', 'desc');
            
            $addons = [];
            // 插件目录
            $addons_path = Env::get('addons_path');
            
            // 获取已存在的插件
            $model = app()->model('Addons');
            $addons_arr = $model->column('id, name, title', 'name');

            // 扫描插件文件夹,以升序排序
            $files = scandir($addons_path);
            $id = 1;
            foreach($files as $file) {
                // 跳过已安装的插件
                if (isset($addons_arr[strtolower($file)])) {
                    continue;
                }
                // 处理未安装的插件
                if ($file != '.' && $file != '..' && is_dir($addons_path . $file)) {
                    if ($object = $this->getInstance($file)) {
                        $addons[] = array_merge(['id' => $id], $object->getInfo(), ['setting' => $object->getConfig()]);
                        $id++;
                    }
                }
            }
            if(!empty($keywords['keywords'])) {
                $list = [];
                $find_name = array_search($keywords['keywords'], array_column($addons, 'name'));
                $find_title = array_search($keywords['keywords'], array_column($addons, 'title'));
                if($find_name !== false) {
                    $list[] = $addons[$find_name];
                }
                if($find_title !== false) {
                    $list[] = $addons[$find_title];
                }
                $list = array_unique($list);
            }
            if(!isset($list) || count($list) === 0) {
                $list = $addons;
            }
            
            $count = count($list); // 总条数 
            $start = ($page-1) * $limit; // 偏移量，当前页-1乘以每页显示条数
            $list = array_slice($list, $start, $limit);
            $this->outcome($list, 1, '查询成功', 'json', ['count' => $count], []);
        }
    }

    /**
     * 快速创建插件
     * @return mixed
     */
    public function create() {
        $model = app()->model('Hooks');
        $hooks = $model::where(['status' => 1])->field('name')->order('id asc')->select();
        $this->assign('hooks', json_encode($hooks));
        $this->assign('meta_title', '快速创建插件');
        return $this->fetch();
    }
    
    /**
     * 预览插件
     */
    public function preview() {
        $data = $this->request->post();
        $model = app()->model('Addons');
        $tpl = $model->data($data)->preview();
        if (false === $tpl) {
            $this->error($model->getError() ?: '预览失败');
        }
        return $this->success($tpl);
    }
    
    /**
     * 添加/更新操作
     */
    public function write() {
        $data = $this->request->post('', '', 'trim');
        $model = app()->model('Addons');
        $ret = $model->data($data)->build();
        if (false !== $ret) {
            $this->success('插件创建成功!', url('Addons/index'));
        }
        $this->error('插件创建失败!');
    }

    /**
     * 安装插件
     */
    public function install() {
        $name = $this->request->param('name', '');
        if (!$name) {
            $this->error('缺少关键参数name!');
        }
        if ($object = $this->getInstance($name)) {
            $data = $object->getInfo();
            $data['setting'] = $object->getConfig();
            $model = app()->model('Addons');
            $hook_model = app()->model('Hooks');
            if ($model::where(['name' => $data['name']])->count()) {
                $this->error('当前插件已存在!');
            }
            // 读取插件目录及钩子列表
            $base = get_class_methods("\\think\\Addons");
            // 读取出所有公共方法
            $methods = (array)get_class_methods($object);
            // 跟插件基类方法做比对，得到差异结果
            $hooks = array_diff($methods, $base);
            // 查询钩子信息
            if (!empty($hooks)) {
                $hooks = $hook_model::where(['name' => $hooks])->select();
                $hooklist = [];
                foreach ($hooks as $hook) {
                    $addons = explode(',', $hook['addons']);
                    array_push($addons, $name);
                    $addons = array_filter(array_unique($addons));
                    $hooklist[] = [
                        'id' => $hook['id'],
                        'addons' => implode(',', $addons)
                    ];
                }
            }
            // 开始事务
            $db = $model->db();
            $db->startTrans();
            try {
                $model->save($data);
                if (isset($hooklist) && !empty($hooklist)) {
                    $hook_model->saveAll($hooklist);
                }
                if (false !== $object->install()) {
                    $db->commit();
                }
            } catch (\Exception $e) {
                // 事务回滚
                $db->rollback();
                $this->error('插件安装异常!');
            }
            $this->success('插件安装成功');
        }
        $this->error('插件安装失败!');
    }

    /**
     * 卸载插件
     */
    public function uninstall() {
        $name = $this->request->param('name', '');
        if (!$name) {
            $this->error('缺少关键参数name!');
        }
        $model = app()->model('Addons');
        $hook_model = app()->model('Hooks');
        
        $info = $model::where(['name' => $name])->find();
        if ($info && $object = $this->getInstance($name)) {
            // 获取所有相关钩子
            $hooks = $hook_model::where('find_in_set(:name, addons)', ['name' => $name])->select();
            $hooklist = [];
            foreach ($hooks as $hook) {
                $addons = explode(',', $hook['addons']);
                $addons = array_diff($addons, [$name]);
                $addons = array_filter(array_unique($addons));
                $hooklist[] = [
                    'id' => $hook['id'],
                    'addons' => implode(',', $addons)
                ];
            }
            // 开始事务
            $db = $model->db();
            $db->startTrans();
            try {
                // 删除插件
                $info->delete();
                // 删除钩子
                if (!empty($hooklist)) {
                    $hook_model->saveAll($hooklist);
                }
                if (false !== $object->uninstall()) {
                    $db->commit();
                }
            } catch (\Exception $e) {
                $db->rollback();
                $this->error('插件卸载异常!');
            }
            $this->success('插件卸载成功!');
        }
        $this->error('插件卸载失败!');
    }

    /**
     * 删除插件
     */
    public function delete() {
        $name = $this->request->param('name', '');
        $addons_path = Env::get('addons_path');
        if ($name) {
            rm_dir($addons_path . $name);
            $this->success('插件删除成功!');
        }
        $this->error('缺少关键参数name!');
    }

    /**
     * 获取插件实例
     * @param type $file 插件名称
     * @return boolean|object
     */
    protected function getInstance($file) {
        $class = "\\addons\\{$file}\\" . ucfirst($file);
        if (class_exists($class)) {
            return Container::get($class);
        }
        return false;
    }
}
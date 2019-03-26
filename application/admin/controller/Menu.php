<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Menu模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\facade\Config;
use think\facade\Cache;

class Menu extends Admin {
    
    /**
     * 菜单规则列表
     * @return type
     */
    public function index() {
        if ($this->request->isAjax()) {
            $model = app()->model('Menu');
            $list = $model->field('id, pid, title, url, icon, status, level, sort')->order('sort')->select();
            $list->append(['status_text', 'level_text']);
            $this->outcome($list, 1, '查询成功', 'json', [], []);
        }
        $this->assign('meta_title', '菜单列表');
        return $this->fetch();
    }
    
    /**
     * 添加菜单
     * @return type
     */
    public function create() {
        $id = $this->request->param('id', 0, 'intval');
        $model = app()->model('Menu');
        // 添加子级
        $msg = '';
        $data = [];
        if(isset($id) && $id) {
            $msg = '子级';
            $data['pid'] = $id;
        }
        $this->assign('menu_select', $this->tree($model, [], $id));
        $this->assign('meta_title', '添加' . $msg . '菜单');
        $this->assign('data', $data);
        return $this->fetch();
    }
    
    /**
     * 编辑菜单
     * @return type
     */
    public function update() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('Menu');
        $data = $model::where(['id' => $id])->field('id, pid, title, sort, url, condition, icon, status, level')->find();
        $this->assign('data', $data);
        $this->assign('menu_select', $this->tree($model, [], $id));
        $this->assign('meta_title', '编辑菜单');
        return $this->fetch('create');
    }
    
    /**
     * 插入/更新操作
     */
    public function write() {
        // 获取post参数
        $data = $this->request->post('', [], 'trim');
        if(isset($data) && count($data) > 0) {
            $model = app()->model('Menu');
            $result = $this->validate($data, 'app\admin\validate\Menu');
            if (true !== $result) {
                $this->error($result);
            }
            //更新操作
            if(isset($data['id']) && $data['id']) {
                $ret = $model->update($data);
                $msg = '编辑';
            } else { //插入操作
                $ret = $model->save($data);
                $msg = '添加';
            }
            if($ret !== false) {
                Cache::clear('menu');
                //记录行为
                //action_log('update_user', 'user', $id, UID);
                $this->success('菜单' . $msg . '成功！', url('Menu/index'));
            } else {
                $this->error('菜单' . $msg . '失败！');
            }
        }
        $this->error('缺少post实体数据!');
    }
    
    /**
     * 删除菜单
     */
    public function delete() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('Menu');
        $ret = $model->where(['id' => $id])->delete();
        if($ret) {
            Cache::clear('menu');
            //记录行为
            //action_log('update_user', 'user', $id, UID);
            $this->success('菜单删除成功！', url('Menu/index'));
        } else {
            $this->error('菜单删除失败！');
        }
    }
    
}
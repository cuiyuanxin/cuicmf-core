<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Auth模块
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
use tree\Tree;

class Auth extends Admin {
    
    /**
     * 角色组列表
     * @return type
     */
    public function index() {
        if ($this->request->isAjax()) {
            $keywords = $this->request->post('key', '');
            $page = $this->request->post('page', 1);
            $limit = $this->request->post('limit', Config::get('cui_config.admin_list_rows'));
            $field = $this->request->post('field', 'id');
            $order = $this->request->post('order', 'desc');
            $model = app()->model('AuthGroup');
            $query = $model->field('id, title, description, status');
            if (!empty($keywords['keywords'])) {
                $query->where('id|title', 'like', $keywords['keywords']);
            }
            $list = $query->order($field, $order)->page($page, $limit)->select();
            $list->append(['status_text']);
            $count = $query->count();
            $this->outcome($list, 1, '查询成功', 'json', ['count' => $count], []);
        }
        $this->assign('meta_title', '角色组列表');
        return $this->fetch();
    }
    
    /**
     * 添加用户组
     * @return type
     */
    public function create() {
        $this->assign('meta_title', '添加用户组');
        return $this->fetch();
    }
    
    /**
     * 编辑用户组
     * @return type
     */
    public function update() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        $model = app()->model('AuthGroup');
        $data = $model->where(['id' => $id])->field('id, title, description, status')->find();
        $this->assign('data', $data);
        $this->assign('meta_title', '编辑用户组');
        return $this->fetch('create');
    }
    
    /**
     * 插入/更新操作
     */
    public function write() {
        //获取post参数
        $data = $this->request->post('', [], 'trim');
        if(isset($data) && count($data) > 0) {
            $model = app()->model('AuthGroup');
            // 验证
            $result = $this->validate($data, 'app\admin\validate\Auth.group');
            if (true !== $result) {
                $this->error($result);
            }
            // 更新操作
            if(isset($data['id']) && $data['id']) {              
                $ret = $model->update($data);
                $msg = '编辑';
                $id = $data['id'];
            } else { //插入操作
                $ret = $model->create($data);
                $msg = '添加';
                $id = 0;
            }
            if ($ret === false) {
                $this->error('用户组' . $msg . '失败！');
            } else {
                //记录行为
    //            action_log('update_user', 'user', $id, UID);
                $this->success('用户组' . $msg . '成功！', url('Auth/index'));
            }
        }
        $this->error('缺少post实体数据!');
    }
    
    /**
     * 删除用户组
     */
    public function delete() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('AuthGroup');
        $db = $model->db();
        $db->startTrans();
        try {
            $model->where(['id' => $id])->delete();
            // 关联删除
            $model->access()->where(['group_id' => $id])->delete();
            $db->commit();
            Cache::clear('menu');
            //记录行为
            //action_log('update_user', 'user', $id, UID);
            $this->success('用户组删除成功！', url('Auth/index'));
        } catch (Exception $e) {
            $db->rollback();
            $this->error('用户组删除失败！');
        }
    }
    
    /**
     * 设置权限
     * @return type
     */
    public function rules() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('Menu');
        $menu = $model::where('level', 'between', '1,3')->field('id, pid, title as name')->select()->toArray();
        $model = app()->model('AuthGroup');
        $rules = $model::where(['id' => $id])->value('rules');
        $tree = new Tree($menu);
        $authtree = $tree->authtree('list', $rules);
        $this->assign('id', $id);
        $this->assign('authtree', $authtree);
        $this->assign('meta_title', '设置权限');
        return $this->fetch();
    }
    
    /**
     * 提交权限
     */
    public function rules_write() {
        //获取post参数
        $data = $this->request->post('', [], 'trim');
        if(isset($data) && count($data) > 0) {
            $model = app()->model('AuthGroup');
            // 验证
            if(isset($data['rules'])) {
                $data['rules'] = implode(',', $data['rules']);
                $ret = $model->update($data);
                if ($ret === false) {
                    $this->error('权限设置失败！');
                } else {
                    Cache::clear('menu');
                    //记录行为
        //            action_log('update_user', 'user', $id, UID);
                    $this->success('权限设置成功！', url('Auth/index'));
                }
            } else {
                $this->error('请选择权限！');
            }
        }
        $this->error('缺少post实体数据!');
    }
}
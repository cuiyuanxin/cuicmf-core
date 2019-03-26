<?php

// +----------------------------------------------------------------------
// | cuicmf 后台Hooks模块
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use think\facade\Config;

class Hooks extends Admin {
    
    /**
     * 钩子管理
     * @return mixed
     */
    public function index() {
        if ($this->request->isAjax()) {
            $keywords = $this->request->post('key', '');
            $page = $this->request->post('page', 1);
            $limit = $this->request->post('limit', Config::get('cui_config.admin_list_rows'));
            $field = $this->request->post('field', 'id');
            $order = $this->request->post('order', 'desc');
            $model = app()->model('Hooks');
            $query = $model->field('id, name, description, status');
            if (!empty($keywords['keywords'])) {
                $query->where('name', 'like', $keywords['keywords']);
            }
            $list = $query->order($field, $order)->page($page, $limit)->select();
            $list->append(['status_text']);
            $count = $query->count();
            $this->outcome($list, 1, '查询成功', 'json', ['count' => $count], []);
        }
        $this->assign('meta_title', '钩子列表');
        return $this->fetch();
    }
    
    /**
     * 添加钩子
     * @return mixed
     */
    public function create() {
        $this->assign('meta_title', '添加钩子');
        return $this->fetch();
    }
    
    /**
     * 编辑钩子
     * @return mixed
     */
    public function update() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('Hooks');
        $data = $model::where(['id' => $id])->field('id, name, description, status')->find();
        $this->assign('data', $data);
        $this->assign('meta_title', '编辑钩子');
        return $this->fetch('create');
    }
    
    /**
     * 插入/更新操作
     */
    public function write() {
        //获取post参数
        $data = $this->request->post('', [], 'trim');
        if(isset($data) && count($data) > 0) {
            $model = app()->model('Hooks');
            // 验证
            $result = $this->validate($data, 'app\admin\validate\Hooks');
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
                $this->error('钩子' . $msg . '失败！');
            } else {
                //记录行为
    //            action_log('update_user', 'user', $id, UID);
                $this->success('钩子' . $msg . '成功！', url('Hooks/index'));
            }
        }
        $this->error('缺少post实体数据!');
    }
    
    /**
     * 删除钩子
     */
    public function delete() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('Hooks');
        $ret = $model->where(['id' => $id])->delete();
        if($ret) {
            //记录行为
            //action_log('update_user', 'user', $id, UID);
            $this->success('钩子删除成功！', url('Hooks/index'));
        } else {
            $this->error('钩子删除失败！');
        }
    }
}

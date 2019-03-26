<?php

// +----------------------------------------------------------------------
// | cuicmf 后台User模块
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

class User extends Admin {
    
    /**
     * 管理员列表
     * @return type
     */
    public function index() {
        if ($this->request->isAjax()) {
            $keywords = $this->request->post('key', '');
            $page = $this->request->post('page', 1);
            $limit = $this->request->post('limit', Config::get('cui_config.admin_list_rows'));
            $field = $this->request->post('field', 'id');
            $order = $this->request->post('order', 'desc');
            $model = app()->model('User');
            if(Config::get('cui_config.user_administrator') == UID) {
                $data = UID;
            } else {
                $data = [
                    Config::get('cui_config.user_administrator'),
                    UID
                ];
                $data = implode(',', $data);
            }
            
            $query = $model->where('id', 'NOT IN', 1)->field('id, username, nickname, realname, mobile, status');
            if (!empty($keywords['keywords'])) {
                $query->where('id|username|mobile', 'like', $keywords['keywords']);
            }
            $list = $query->order($field, $order)->page($page, $limit)->select();
            $list->append(['status_text', 'auth_group_text']);
            $count = $query->count();
            $this->outcome($list, 1, '查询成功', 'json', ['count' => $count], []);
        }
        $this->assign('meta_title', '管理员列表');
        return $this->fetch();
    }
    
    /**
     * 添加管理员
     * @return type
     */
    public function create() {
        $model = app()->model('AuthGroup');
        $auth_group = $model->where(['status' => 1])->field('id, title as name')->select();
        $this->assign('auth_group', json_encode($auth_group));
        $this->assign('meta_title', '添加管理员');
        return $this->fetch();
    }
    
    /**
     * 编辑管理员
     * @return type
     */
    public function update() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('AuthGroup');
        $auth_group = $model->where(['status' => 1])->field('id, title as name')->select();
        $this->assign('auth_group', json_encode($auth_group));
        
        $model = app()->model('User');
        $data = $model::where(['id' => $id])->field('id, username, realname, nickname, mobile, email, status')->find();
        $data['access'] = json_encode($data->access()->where(['uid' => $id])->column('group_id'));
        $this->assign('data', $data);
        $this->assign('meta_title', '编辑管理员');
        return $this->fetch('create');
    }
    
    /**
     * 插入/更新操作
     */
    public function write() {
        //获取post参数
        $data = $this->request->post('', [], 'trim');
        if(isset($data) && count($data) > 0) {
            $model = app()->model('User');
            $db = $model->db();
            //更新操作
            if(isset($data['id']) && $data['id']) {
                $result = $this->validate($data, 'app\admin\validate\User.update');
            } else {
                $result = $this->validate($data, 'app\admin\validate\User.create');
            }
            if (true !== $result) {
                $this->error($result);
            }
            // 格式化用户组
            foreach(explode(',', $data['group_id']) as $key => $value) {
                $group_id_arr[$key]['group_id'] = $value; 
            }

            //更新操作
            if(isset($data['id']) && $data['id']) {
                $user_data = [
                    'id' => $data['id'],
                    'realname' => $data['realname'],
                    'nickname' => $data['nickname'],
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'status' => $data['status']
                ];
                $db->startTrans();
                try {
                    $model->update($user_data);
                    // 关联更新
                    $model->access()->where(['uid' => $data['id']])->delete();
                    array_walk($group_id_arr, function(&$value, $key, $id) { 
                        $value = array_merge($value, ['uid' => $id]); 
                    }, $data['id']);
                    $model->access()->saveAll($group_id_arr);
                    $db->commit();
//                    //记录行为
//                    //action_log('update_user', 'user', $id, UID);
                    $this->success('管理员编辑成功！', url('User/index'));
                } catch (Exception $e) {
                    $db->rollback();
                    $this->error('管理员编辑失败！');
                }
            } else { //插入操作
                $user_data = [
                    'username' => $data['username'],
                    'password' => $data['password'],
                    'realname' => $data['realname'],
                    'nickname' => $data['nickname'],
                    'mobile' => $data['mobile'],
                    'email' => $data['email'],
                    'status' => $data['status']
                ];
                $db->startTrans();
                try {
                    $model->save($user_data);
                    // 关联插入
                    array_walk($group_id_arr, function(&$value, $key, $id) { 
                        $value = array_merge($value, ['uid' => $id]); 
                    }, $model->id);
                    $model->access()->saveAll($group_id_arr);
                    $db->commit();
                    //记录行为
                    //action_log('update_user', 'user', $id, UID);
                    $this->success('管理员添加成功！', url('User/index'));
                } catch (Exception $e) {
                    $db->rollback();
                    $this->error('管理员添加失败！');
                }
            }
        }
        $this->error('缺少post实体数据!');
    }
    
    /**
     * 管理员删除
     */
    public function delete() {
        $id = $this->request->param('id', 0, 'intval');
        if(isset($id) && !$id) $this->error('缺少关键参数ID!');
        
        $model = app()->model('User');
        $db = $model->db();
        $db->startTrans();
        try {
            $model->where(['id' => $id])->delete();
            // 关联删除
            $model->access()->where(['uid' => $id])->delete();
            $db->commit();
            Cache::clear('menu');
            //记录行为
            //action_log('update_user', 'user', $id, UID);
            $this->success('管理员删除成功！', url('User/index'));
        } catch (Exception $e) {
            $db->rollback();
            $this->error('管理员删除失败！');
        }
    }
}
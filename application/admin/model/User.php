<?php

// +----------------------------------------------------------------------
// | cuicmf User模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;
use think\facade\Config;
use think\facade\Request;

class User extends Model {
    
    // 设置主键
//    protected $pk = 'uid';
    // 设置当前模型对应的完整数据表名称
//    protected $table = 'think_user';
    // 设置当前模型的数据库连接
//    protected $connection = 'db_config';
    // 设置json类型字段
//    protected $json = ['info'];
    // 设置JSON字段的类型
//    protected $jsonType = [
//    	'info->user_id'	=>	'int'
//    ];
    // 设置JSON数据返回数组
//    protected $jsonAssoc = true;
    // 自动写入数间戳
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
//    protected $createTime = 'create_at';
//    protected $updateTime = 'update_at';
    // 关闭自动写入update_time字段
//    protected $updateTime = false;
    // 设置只读字段
//    protected $readonly = ['name', 'email'];
    // 软删除
//    use SoftDelete;
    // 设置软删除时间
//    protected $deleteTime = 'delete_time';
    // 软删除默认数据
//    protected $defaultSoftDelete = 0;
    // 数据类型字段转换
    protected $type = [
        'id' => 'intval',
        'status' => 'integer'
    ];
    // 自动完成包含新增和更新操作
    protected $auto = ['nickname', 'realname'];
    // 自动完成新增操作
    protected $insert = ['password', 'reg_ip'];  
    // 自动完成更新操作
    protected $update = [];  
    // 定义全局的查询范围
//    protected $globalScope = ['status'];
    
    // 模型初始化
    protected static function init() {
        //TODO:初始化内容
    }
    
    /**
     * 关联用户关系表
     * @return type
     */
    public function access() {
        return $this->hasMany('AuthGroupAccess', 'uid');
    }
    
    /**
     * 返回group_id角色组转换文本
     * @param type $value
     * @return string
     */
    public function getAuthGroupTextAttr($value, $data) {
        if(Config::get('cui_config.user_administrator') == $data['id']) {
            return '系统管理员';
        } else {
            $group_id = $this->access()->where(['uid' => $data['id']])->column('group_id');
            $title = $this->db()->name('auth_group')->where('id', 'in', implode(',', $group_id))->order('id')->column('title');
            return implode(',', $title);
        }
    }
    
    /**
     * 返回status状态转换文本
     * @param type $value
     * @return string
     */
    public function getStatusTextAttr($value, $data) {
        $status = [
            0 => '禁用',
            1 => '正常',
            2 => '锁定'
        ];
        return $status[$data['status']];
    }
    
    /**
     * 自动完成密码加密
     * @param type $value
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function setPasswordAttr($value) {
        return cui_ucenter_md5($value, Config::get('cui_config.uc_auth_key'));
    }
    
    /**
     * 自动完成nickname
     * @param type $value
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function setNicknameAttr($value) {
        $data = self::getData();
        if (isset($data['id'])) {
            if (!$value) {
                return self::where(['id' => $data['id']])->value('username');
            }
        } else {
            if (isset($data['username']) && !$value) {
                return $data['username'];
            }
        }
        return $value;
    }
    
    /**
     * 自动完成realname
     * @param type $value
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function setRealnameAttr($value) {
        $data = self::getData();
        if (isset($data['id'])) {
            if (!$value) {
                return self::where(['id' => $data['id']])->value('username');
            }
        } else {
            if (isset($data['username']) && !$value) {
                return $data['username'];
            }
        }
        return $value;
    }
    
    /**
     * 自动完成注册ip
     * @param type $value
     * @return type
     * @author 崔元欣 <15811506097@163.com>
     */
    protected function setRegIpAttr($value) {
        return ipToint(Request::ip());
    }
}
<?php

// +----------------------------------------------------------------------
// | cuicmf AuthGroupAccess模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;

class AuthGroupAccess extends Model {
    
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
    protected $autoWriteTimestamp = false;
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
        'uid' => 'intval',
        'group_id' => 'integer'
    ];
    // 自动完成包含新增和更新操作
    protected $auto = [];
    // 自动完成新增操作
    protected $insert = [];  
    // 自动完成更新操作
    protected $update = [];  
    // 定义全局的查询范围
//    protected $globalScope = ['status'];
    
    // 模型初始化
    protected static function init() {
        //TODO:初始化内容
    }
    
    /**
     * 与AuthGroup定义相对关联
     * @return type
     */
    public function group() {
        return $this->belongsTo('AuthGroup');
    }
    
    /**
     * 与AuthGroup定义相对关联
     * @return type
     */
//    public function user() {
//        return $this->belongsTo('User', 'id', 'uid');
//    }
    
    /**
     * 返回group_id角色组转换文本
     * @param type $value
     * @return string
     */
//    public function getAuthGroupTextAttr($value, $data) {
//        return Config::get('cui_config.user_administrator') == $data['id'] ? '系统管理员' : '其他管理员';
//    }
    
}
<?php

// +----------------------------------------------------------------------
// | cuicmf Addons模型
// +----------------------------------------------------------------------
// | Copyright (c) 2018-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 崔元欣 <15811506097@163.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;
use think\facade\Env;

class Addons extends Model {
    
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
        'status' => 'integer',
        'is_admin' => 'integer',
        'is_index' => 'integer',
        'setting' => 'json'
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
     * 关联用户关系表
     * @return type
     */
//    public function access() {
//        return $this->hasMany('AuthGroupAccess', 'group_id');
//    }
    
    /**
     * 返回status状态转换文本
     * @param type $value
     * @return string
     */
    public function getStatusTextAttr($value, $data) {
        $status = [
            0 => '禁用',
            1 => '正常'
        ];
        return $status[$data['status']];
    }
    
    /**
     * 返回create_time时间转换文本
     * @param type $value
     * @return string
     */
    public function getCreateTextAttr($value, $data) {
        return date('Y-m-d H:i:s', $data['create_time']);
    }
    
    /**
     * 返回setting配置长度
     * @param type $value
     * @return string
     */
    public function getSettingLengthAttr($value, $data) {
        return count(json_decode($data['setting'], true));
    }
    
    /**
     * 预览插件
     * @param array $data 源数据
     * @return bool|string
     */
    public function preview($data = [])
    {
        $data = array_merge($this->getData(), $data);
        $data['status'] = 1;
        $data['hooks'] = isset($data['hooks']) ? explode(',', $data['hooks']) : [];

        $hook = '';
        foreach ($data['hooks'] as $value) {
            $hook .= <<<str
    // 实现的 {$value} 钩子方法
    public function {$value}(\$param){
    
    }

str;
        }
        $classname = ucfirst($data['name']);
        $namespace = 'addons\\' . $data['name'];
        $tpl = <<<str
<?php

namespace {$namespace};
use think\Addons;

/**
 * {$data['title']}插件
 * @author {$data['author']}
 */
class {$classname} extends Addons
{
    public \$info = array(
        'name'=>'{$data['name']}',
        'title'=>'{$data['title']}',
        'description'=>'{$data['description']}',
        'status'=>{$data['status']},
        'author'=>'{$data['author']}',
        'version'=>'{$data['version']}'
    );

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

{$hook}
}
str;
        return $tpl;
    }

    /**
     * 创建插件
     * @param type $data 源数据
     * @return boolean
     */
    public function build($data = []) {
        $data = array_merge($this->getData(), $data);
        
        $addonFile = $this->preview ();
        $addons_path = Env::get('addons_path');
        
        // 创建目录结构
        $files = array ();
        $addon_dir = "$addons_path{$data['name']}/";
        $files [] = $addon_dir;
        $addon_name = ucfirst($data['name']).".php";

        // 如果有前后台入口
        if (isset($data['is_admin']) || isset($data['is_index'])) {
            $files[] = "{$addon_dir}controller/";
            $files[] = "{$addon_dir}model/";
        }

        foreach ($files as $dir) {
            if (!mk_dir($dir)) {
                $this->error = '插件' . $data['name'] . '目录存在';
                return false;
            }
        }

        // 写文件
        file_put_contents( "{$addon_dir}{$addon_name}", $addonFile);

        // 如果有配置文件
        if (isset($data['is_config'] ) && $data['is_config'] == 1) {
            $config = <<<str
<?php
// 插件配置
return [
	'title'     => [//配置在表单中的键名 ,这个会是config[title]
		'title' => '显示标题:',//表单的文字
		'type'  => 'text',		 //表单的类型：text、textarea、checkbox、radio、select等
		'value' => '系统信息',			 //表单的默认值
	],
	'display'   => [
		'title' => '是否显示:',
		'type'  => 'radio',
		'options'   => [
			'1' => '显示',
			'0' => '不显示'
		],
		'value' => '1'
	]
];
str;
            file_put_contents($addon_dir . 'config.php', $config);
        }

        // 如果存在后台
        if (isset($data['is_admin']) && $data['is_admin'] == 1) {
            $adminController = <<<str
<?php
namespace addons\demo\controller;

use think\addons\Controller;

class Admin extends Controller
{
    // 索引入口
    public function index()
    {
        return 'hello addons admin';
    }
}
str;
            file_put_contents("{$addon_dir}controller/admin.php", $adminController);
        }

        // 如果存在前台
        if (isset($data['is_index']) && $data['is_index'] == 1) {
            $indexController = <<<str
<?php
namespace addons\demo\controller;

use think\addons\Controller;

class Index extends Controller
{
    // 索引入口
    public function index()
    {
        return 'hello addons index';
    }
}

str;
            file_put_contents("{$addon_dir}controller/index.php", $indexController);
        }

        return true;
    }

}
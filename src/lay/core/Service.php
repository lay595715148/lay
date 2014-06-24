<?php
/**
 * 业务逻辑处理类
 * 
 * @abstract
 * @author Lay Li
 */
namespace lay\core;

use lay\core\Store;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 业务逻辑处理类
 * 
 * @abstract
 * @author Lay Li
 */
abstract class Service extends AbstractService {
    /**
     * 事件常量，创建时
     * @var string
     */
    const E_CREATE = 'service_create';
    /**
     * 钩子常量，创建时
     * @var string
     */
    const H_CREATE = 'hook_service_create';
    
    /**
     * 业务逻辑对象数组
     * @var array
     */
    protected static $_Instances = array();
    /**
     * 通过类名，获取一个业务逻辑对象实例
     * @param string $classname 继承Service的类名
     * @return Service
     */
    public static function getInstance($classname) {
        if(empty(self::$_Instances[$classname])) {
            $instance = new $classname();
            if(is_subclass_of($instance, 'lay\core\Service')) {
                self::$_Instances[$classname] = $instance;
            } else {
                unset($instance);
            }
        }
        return self::$_Instances[$classname];
    }
    /**
     * 获取一个新业务逻辑对象实例
     * @param string $classname 继承Service的类名
     * @return Service
     */
    public static function newInstance($classname) {
        $instance = new $classname();
        if(is_subclass_of($instance, 'lay\core\Service')) {
            return $instance;
        } else {
            unset($instance);
            return false;
        }
    }
    /**
     * 数据访问对象，为主表（或其他）数据模型的数据访问对象
     * 
     * @var Store
     */
    protected $store;
    /**
     * 构造方法
     * @param Store $store 数据库访问对象
     */
    public function __construct($store = '') {
        if($store && is_a($store, 'lay\core\Store')) {
            $this->store = $store;
        }
        PluginManager::exec(Service::H_CREATE, array($this));
        EventEmitter::emit(Service::E_CREATE, array($this));
    }
    /**
     * 获取某条记录
     * 
     * @param int|string $id
     *            ID
     * @return array
     */
    public function get($id) {
        return $this->store->get($id);
    }
    /**
     * 增加一条记录
     * 
     * @param array $info
     *            数据数组
     * @return boolean
     */
    public function add(array $info) {
        return $this->store->add($info);
    }
    /**
     * 删除某条记录
     * 
     * @param int|string $id
     *            ID
     * @return boolean
     */
    public function del($id) {
        return $this->store->del($id);
    }
    /**
     * 更新某条记录
     * 
     * @param int|string $id
     *            ID
     * @param array $info
     *            数据数组
     * @return boolean
     */
    public function upd($id, array $info) {
        return $this->store->upd($id, $info);
    }
    /**
     * 某些条件下的记录数
     * 
     * @param array $info
     *            数据数组
     * @return int
     */
    public function count(array $info) {
        return $this->store->count($info);
    }
}
?>

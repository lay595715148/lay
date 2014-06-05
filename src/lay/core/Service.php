<?php
namespace lay\core;

use lay\core\Store;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 业务逻辑处理类
 * 
 * @author Lay Li
 */
abstract class Service extends AbstractService {
    /**
     * 事件常量，服务对象创建时
     * @var string
     */
    const E_CREATE = 'service_create';
    /**
     * 钩子常量，服务对象创建时
     * @var string
     */
    const H_CREATE = 'hook_service_create';
    
    /**
     * 受保护的静态的服务对象数组
     * @var array
     */
    protected static $_Instances = array();
    /**
     * 通过类名，获取一个服务对象实例
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
     * 获取一个新Store实例
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
     * 为主表（或其他）数据模型的数据访问对象
     * 
     * @var Store
     */
    protected $store;
    public function __construct($store) {
        if(is_subclass_of($store, 'lay\core\Store')) {
            $this->store = $store;
        }
        PluginManager::exec(Service::H_CREATE, array($this));
        EventEmitter::emit(Service::E_CREATE, array($this));
    }
    /**
     * 获取某条记录
     * 
     * @param int|string $id
     *            the ID
     */
    public function get($id) {
        return $this->store->get($id);
    }
    /**
     * 增加一条记录
     * 
     * @param array $info
     *            information array
     */
    public function add(array $info) {
        return $this->store->add($info);
    }
    /**
     * 删除某条记录
     * 
     * @param int|string $id
     *            the ID
     */
    public function del($id) {
        return $this->store->del($id);
    }
    /**
     * 更新某条记录
     * 
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public function upd($id, array $info) {
        return $this->store->upd($id, $info);
    }
    public function count(array $info) {
        return $this->store->count($info);
    }
}
?>

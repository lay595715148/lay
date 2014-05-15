<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 业务逻辑处理类
 * 
 * @author Lay Li
 */
abstract class Service extends AbstractService {
    const EVENT_CREATE = 'service_create';
    const HOOK_CREATE = 'hook_service_create';
    
    protected static $_Instances = array();
    /**
     * 获取池中的一个Store实例
     * @param string $classname
     * @return Service
     */
    public static function getInstance($classname) {
        if(empty(self::$_Instances[$classname])) {
            $instance = new $classname();
            if(is_subclass_of($instance, 'Service')) {
                self::$_Instances[$classname] = $instance;
            } else {
                unset($instance);
            }
        }
        return self::$_Instances[$classname];
    }
    /**
     * 获取一个新Store实例
     * @param Model $model
     * @param string $classname
     * @return Service
     */
    public static function newInstance($classname) {
        $instance = new $classname();
        if(is_subclass_of($instance, 'Service')) {
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
        $this->store = $store;
        PluginManager::exec(Service::HOOK_CREATE, array($this));
        EventEmitter::emit(Service::EVENT_CREATE, array($this));
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

<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 数据库访问类
 * 
 * @author Lay Li
 */
abstract class Store extends AbstractStore {
    const EVENT_CREATE = 'store_create';
    const HOOK_CREATE = 'hook_store_create';
    /**
     * 数据访问对象数组
     * 
     * @var array<Store>
     */
    protected static $_Instances = array();
    /**
     * 获取池中的一个Store实例
     * @param string $classname
     * @return Store
     */
    public static function getInstance($classname) {
        if(empty(self::$_Instances[$classname])) {
            $instance = new $classname();
            if(is_subclass_of($instance, 'Store')) {
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
     * @return Store
     */
    public static function newInstance($classname) {
        $instance = new $classname();
        if(is_subclass_of($instance, 'Store')) {
            return $instance;
        } else {
            unset($instance);
            return false;
        }
    }
    /**
     * close all connections
     * 
     * @return boolean
     */
    public static function closeAll() {
        foreach(self::$_Instances as $name => $instance) {
            $instance->close();
        }
        self::$_Instances = array();
        return true;
    }
    /**
     * 名称，唯一
     * 
     * @var string
     */
    protected $name;
    /**
     * 模型对象
     * 
     * @var Model
     */
    protected $model;
    /**
     * schema
     * 
     * @var string schema
     */
    protected $schema;
    /**
     * 配置数组
     * 
     * @var array
     */
    protected $config = array();
    /**
     * database connection resource
     * 
     * @var #resource
     */
    protected $link;
    /**
     * database query result
     * @var #resource
     */
    protected $result;
    public function __construct($name, $model, $config = array()) {
        $this->name = $name;
        $this->model = is_subclass_of($model, 'Model') ? $model : false;
        $this->config = is_array($config) ? $config : array();
        $this->schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
        PluginManager::exec(self::HOOK_CREATE, array(
                $this
        ));
        EventEmitter::emit(self::EVENT_CREATE, array(
                $this
        ));
    }
    
    /**
     *
     * @param Model $model            
     */
    public function setModel($model) {
        if(is_subclass_of($model, 'Model'))
            $this->model = $model;
    }
    /**
     *
     * @param Model $model            
     */
    public function getModel($model) {
        return $this->model;
    }
}
?>

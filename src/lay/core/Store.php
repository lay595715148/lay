<?php
/**
 * 数据库访问类
 * 
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 数据库访问类
 * 
 * @author Lay Li
 * @abstract
 */
abstract class Store extends AbstractStore {
    /**
     * 事件常量，数据访问对象创建时
     * @var string
     */
    const E_CREATE = 'store_create';
    /**
     * 钩子常量，数据访问对象创建时
     * @var string
     */
    const H_CREATE = 'hook_store_create';
    /**
     * 获取池中的一个数据访问对象实例
     * @param string $classname 类名
     * @return Store
     */
    public static function getInstance($classname = '') {
        if(empty($classname)) {
            return parent::getInstance();
        }
        if(empty(self::$_SingletonStack[$classname])) {
            $instance = new $classname();
            if(is_a($instance, 'lay\core\Store')) {
                self::$_SingletonStack[$classname] = $instance;
            } else {
                unset($instance);
            }
        }
        return self::$_SingletonStack[$classname];
    }
    /**
     * 获取一个新数据访问对象实例
     * @param Model $model 模型对象
     * @param string $classname 类名
     * @return Store
     */
    public static function newInstance($classname) {
        if(empty($classname)) {
            $classname = get_called_class();
        }
        $instance = new $classname();
        if(is_subclass_of($instance, 'lay\core\Store')) {
            return $instance;
        } else {
            unset($instance);
            return false;
        }
    }
    /**
     * 关闭所有数据库连接句柄
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
     * 名称
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
     * 数据库连接句柄
     * 
     * @var mixed
     */
    protected $link;
    /**
     * 数据库查询结果集
     * @var mixed
     */
    protected $result;
    /**
     * 构造方法
     * @param string $name 名称
     * @param Model $model 模型对象
     * @param array $config 配置数组
     */
    protected function __construct($name, $model, $config = array()) {
        $this->name = $name;
        $this->model = is_subclass_of($model, 'lay\core\Model') ? $model : false;
        $this->config = is_array($config) ? $config : array();
        $this->schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
        PluginManager::exec(self::H_CREATE, array(
                $this
        ));
        EventEmitter::emit(self::E_CREATE, array(
                $this
        ));
    }
    
    /**
     * 设置模型对象
     * @param Model $model 模型对象
     * @return void
     */
    public function setModel($model) {
        if(is_a($model, 'lay\core\Model'))
            $this->model = $model;
    }
    /**
     * 获取模型对象
     * @return Model
     */
    public function getModel() {
        return $this->model;
    }
    /**
     * 获取名称
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}
?>

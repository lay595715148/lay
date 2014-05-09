<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 数据库访问类
 * 
 * @author Lay Li
 */
abstract class Store {
    const EVENT_CREATE = 'store_create';
    const HOOK_CREATE = 'hook_store_create';
    /**
     * 数据访问对象数组
     * 
     * @var array<Store>
     */
    protected static $_Instances = array();
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
     * 模型对象
     * 
     * @var Model
     */
    protected $model;
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
    public function __construct($model, $config = array()) {
        $this->model = is_subclass_of($model, 'Model') ? $model : false;
        $this->config = is_array($config) ? $config : array();
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
    /**
     * 连接数据库
     */
    public abstract function connect();
    /**
     * 切换数据库
     *
     * @param string $name
     *            名称
     */
    public abstract function change($name = '');
    /**
     * do database querying
     *
     * @param fixed $sql
     *            SQL或其他查询结构
     * @param string $encoding
     *            编码
     * @param boolean $showinfo
     *            是否记录查询信息
     */
    public abstract function query($sql, $encoding = '', $showinfo = false);
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     */
    public abstract function get($id);
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     */
    public abstract function del($id);
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     */
    public abstract function add(array $info);
    /**
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public abstract function upd($id, array $info);
    /**
     * close connection
     */
    public abstract function close();
}
?>

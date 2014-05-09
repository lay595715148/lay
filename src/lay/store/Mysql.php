<?php
if(! defined('INIT_LAY')) {
    exit();
}

class Mysql extends Store {
    /**
     * 获取池中的一个Mysql实例
     * @param Model $model
     * @param string|array $name
     * @return Mysql
     */
    public static function getInstance(Model $model, $name = 'default') {
        if(empty(self::$_Instances[$name])) {
            self::$_Instances[$name] = new Mysql($model, $name);
        } else {
            self::$_Instances[$name]->setModel($model);
        }
        return self::$_Instances[$name];
    }
    /**
     * 获取一个新Mysql实例
     * @param Model $model
     * @param string|array $name
     * @return Mysql
     */
    public static function newInstance(Model $model, $name = 'default') {
        return new Mysql($model, $name);
    }
    
    public function __construct($model, $name) {
        if(is_string($name)) {
            $config = Lay::get('stores.'.$name);
        } else if(is_array($name)) {
            $config = $name;
        }
        parent::__construct($model, $config);
    }
    /**
     * 连接Mysql数据库
     */
    public function connect() {
        $config = $this->config;
        $host = isset($config['host']) ? $config['host'] : 'localhost';
        $port = isset($config['port']) ? $config['port'] : 3306;
        $username = isset($config['username']) && is_string($config['username']) ? $config['username'] : '';
        $password = isset($config['password']) && is_string($config['password']) ? $config['password'] : '';
        $newlink = isset($config['newlink']) && is_string($config['newlink']) ? $config['newlink'] : false;
        $database = isset($config['database']) && is_string($config['database']) ? $config['database'] : '';
        
        try {
            $this->link = mysql_connect($host . ':' . $port, $username, $password, $newlink);
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString());
            return false;
        }
        return mysql_select_db($database, $this->link);
    }
    /**
     * 切换Mysql数据库
     *
     * @param string $name
     *            名称
     */
    public function change($name = '') {
    }
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
    public function query($sql, $encoding = '', $showinfo = false) {
    }
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     */
    public function get($id) {
    }
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     */
    public function del($id) {
    }
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     */
    public function add(array $info) {
    }
    /**
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public function upd($id, array $info) {
    }
    /**
     * close connection
     */
    public function close() {
        if($this->link)
            mysql_close($this->link);
    }
}
?>

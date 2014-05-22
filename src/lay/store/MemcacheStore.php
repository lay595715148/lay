<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * Memcache Store
 * @author Lay Li
 */
class MemcacheStore extends Store {
    public function __construct($model, $name = 'memcache') {
        if(is_string($name)) {
            $config = App::get('stores.'.$name);
        } else if(is_array($name)) {
            $config = $name;
        }
        parent::__construct($name, $model, $config);
    }
    
    /**
     * 
     * @var MongoClient
     */
    protected $link;
    /**
     * 连接Mongo数据库
     */
    public function connect() {
    
        try {
            $this->link = Connection::memcache($this->name, $this->config);
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString());
            return false;
        }
        return true;
    }
    /**
     * 切换Memcache
     *
     * @param string $name
     *            名称
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $this->link = Connection::memcache($name, $config);
        } else {
            $this->connect();
        }
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
     *
     * @param array $info
     *            information array
     */
    public function count(array $info = array()) {
    }
    /**
     * close connection
     */
    public function close() {
        if($this->link)
            $this->link->close();
    }
}
?>

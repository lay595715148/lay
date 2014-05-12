<?php
if(! defined('INIT_LAY')) {
    exit();
}

class Mongo extends Store {
    public function __construct($model, $name = 'mongo') {
        if(is_string($name)) {
            $config = Lay::get('stores.'.$name);
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
        //$config = $this->config;
        //$options = array();
        //$host = isset($config['host']) ? $config['host'] : 'localhost';
        //$port = isset($config['port']) ? $config['port'] : 27017;
        //$options['username'] = isset($config['username']) && is_string($config['username']) ? $config['username'] : '';
        //$options['password'] = isset($config['password']) && is_string($config['password']) ? $config['password'] : '';
        //$database = isset($config['schema']) && is_string($config['database']) ? $config['database'] : '';
        //$options['authSource'] = '';
    
        try {
            $this->link = Connection::mongo($this->name, $this->config);
            $this->link->selectDB($this->schema);
            $this->link->connect();
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString());
            return false;
        }
        return true;
    }
    /**
     * 切换Mongo数据库
     *
     * @param string $name
     *            名称
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
            $this->link = Connection::mongo($name, $config);
            $this->link->selectDB($schema);
            $this->link->connect();
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
    public function count(array $info) {
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

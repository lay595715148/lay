<?php
if(! defined('INIT_LAY')) {
    exit();
}

class MongoStore extends Store {
    public function __construct($model, $name = 'mongo') {
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
    private $connection;
    /**
     * 
     * @var MongoDB
     */
    protected $link;
    /**
     * 连接Mongo数据库
     */
    public function connect() {
        try {
            $this->connection = Connection::mongo($this->name, $this->config);
            //$this->link = $this->connection->connection;
            $this->link = $this->connection->connection->selectDB($this->schema);
            //$this->link->connect();
        } catch (Exception $e) {
            Logger::error($e);
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
            $this->connection = Connection::mongo($this->name, $this->config)->connection;
            //$this->link = Connection::mongo($name, $config);
            $this->link = $this->connection->selectDB($schema);
            //$this->link->connect();
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
    public function query($sql, $encoding = '', $showsql = false) {
        $config = &$this->config;
        $result = &$this->result;
        $connection = &$this->connection;
        $link = &$this->link;
        if(! $link) {
            $this->connect();
        }
        
        if(! $encoding && $config['encoding']) {
            $encoding = $config['encoding'];
        }
        if(! $showsql && $config['showsql']) {
            $showsql = $config['showsql'];
        }
        if($encoding && $connection->encoding != $encoding) {
            if($showsql) {
                Logger::info('SET NAMES ' . $encoding, 'MONGO');
            }
            $connection->encoding = $encoding;
            //mysql_query('SET NAMES ' . $encoding, $link);
            //mysqli_query($link, 'SET NAMES ' . $encoding);
        }
        if($showsql) {
            Logger::info($sql, 'MONGO');
        }
        if($sql) {
            $result = $link->execute($sql);
            //$result = mysql_query($sql, $link);
        }
        
        return $result;
    }
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     */
    public function get($id) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $result = $link->selectCollection($table)->findOne(array($pk => $id));
        //$sql = "db.$table.find({\"$pk\":$id})";var_dump($sql);
        //$result = $link->execute($sql);
        //$result = $link->listCollections();
        return $result;
    }
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     */
    public function del($id) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }

        $result = $link->selectCollection($table)->remove(array($pk => $id));
        return $result;
    }
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     */
    public function add(array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }

        $result = $link->selectCollection($table)->insert($info);
        return $result;
    }
    /**
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public function upd($id, array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }

        $result = $link->selectCollection($table)->update(array($pk => $id), $info);
        return $result;
    }
    /**
     *
     * @param array $info
     *            information array
     */
    public function count(array $info = array()) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $sql = "db.$table.count()";;
        $result = $this->query($sql, 'UTF8', true);
        return $result['retval'];
    }
    /**
     * find and modify
     */
    public function fam() {
        
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

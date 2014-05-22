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
     * auto increment sequence table name
     * @var string
     */
    private $sequence;
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
            $this->sequence = is_string($this->config['sequence']) ? $this->config['sequence'] : 'lay_sequence';
            $this->link = $this->connection->connection->{$this->schema};
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
            $schema = is_string($config['schema']) ? $config['schema'] : '';
            $this->connection = Connection::mongo($name, $config)->connection;
            $this->sequence = is_string($config['sequence']) ? $config['sequence'] : 'lay_sequence';
            $this->link = $this->connection->$schema;
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

        $sql = "return db.lay_user.find({}, {_id:1}).sort({_id:-1}).skip(5).limit(5)";
        $ret = $link->execute($sql);var_dump($ret['retval']);
        
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

        $coder = new Coder($model, $link);
        $coder->setQuery(array($pk => $id));
        $result = $coder->makeDelete();
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
        $columns = $model->columns();
        $pk = $model->primary();
        $seq = is_subclass_of($model, 'I_Increment') ? $model->sequence() : '';
        if(! $link) {
            $this->connect();
        }

        $coder = new Coder($model, $link);
        if($seq) {
            $k = array_search($seq, $columns);
            if(!array_key_exists($seq, $info) && !array_key_exists($columns[$k], $info)) {
                $new = $this->nextSequence();
                $info[$seq] = $new;
            }
        }
        $coder->setValues($info);
        $result = $coder->makeInsert();
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

        $coder = new Coder($model, $link);
        $coder->setSetter($info);
        $coder->setQuery(array($pk => $id));
        $result = $coder->makeUpdate();
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

        $coder = new Coder($model, $link);
        $coder->setQuery($info);
        $result = $coder->makeCount();
        return $result;
    }
    /**
     * find 
     */
    public function select($query, $fields, $sort = array(), $limit = array()) {
        return $this->find($query, $fields, $sort, $limit);
    }
    public function find($query, $fields, $sort = array(), $limit = array()) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $coder = new Coder($model, $link);
        $coder->setQuery($query);
        $coder->setFields($fields);
        $coder->setOrder($sort);
        $coder->setLimit($limit);
        $coder->makeSelect();
        return $coder->makeIterator();
    }
    /**
     * find and modify
     */
    public function findModify($query, $setter, $fields = array(), $new = false) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        if(! $link) {
            $this->connect();
        }

        $coder = new Coder($model, $link);
        $coder->setFields($fields);
        $coder->setQuery($query);
        $coder->setSetter($setter);
        $coder->setNew($new);
        $result = $coder->makeFindModify();
        return $result;
    }
    /**
     * close connection
     */
    public function close() {
        if($this->link)
            $this->link->close();
    }
    protected function nextSequence($step = 1) {Logger::debug('do next');
        if(!is_subclass_of($this->model, 'I_Increment')) {
            return false;
        }
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        $seq = $model->sequence();
        if(! $link) {
            $this->connect();
        }

        $seqStore = new MongoStore(new MongoSequence(), $this->name);
        $seqquery = array('name' => $table.'.'.$seq);
        $ret = $seqStore->findModify(array('name' => $table.'.'.$pk), array('$inc' => array('seq' => $step)), array('seq'), true);
        return $ret['seq'];
    }
}
?>

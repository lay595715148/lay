<?php
/**
 * 操作mongodb数据库类
 *
 * @author Lay Li
 */
namespace lay\store;

use lay\App;
use lay\core\Connection;
use lay\core\Increment;
use lay\core\Coder;
use lay\core\Store;
use lay\model\MongoSequence;
use lay\util\Logger;
use Mongo;
use MongoClient;
use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 操作mongodb数据库类
 *
 * @author Lay Li
 */
class MongoStore extends Store {
    /**
     * 构造方法
     *
     * @param Model $model
     *            模型对象
     * @param string $name
     *            名称
     */
    public function __construct($model, $name = 'mongo') {
        if(is_string($name)) {
            $config = App::get('stores.' . $name);
        } else if(is_array($name)) {
            $config = $name;
        }
        parent::__construct($name, $model, $config);
    }
    
    /**
     * 标记自增涨字段的数据库表名
     *
     * @var string
     */
    protected $sequence;
    /**
     * 数据库连接对象
     *
     * @var MongoClient
     */
    protected $connection;
    /**
     * 数据库访问对象
     *
     * @var MongoDB
     */
    protected $link;
    /**
     * 连接MongoDB数据库
     *
     * @return boolean
     */
    public function connect() {
        try {
            $this->connection = Connection::mongo($this->name, $this->config)->link;
            $this->sequence = is_string($this->config['sequence']) ? $this->config['sequence'] : 'lay_sequence';
            $this->link = $this->connection->{$this->schema};
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
     * @return boolean
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $schema = is_string($config['schema']) ? $config['schema'] : '';
            $this->connection = Connection::mongo($name, $config)->link;
            $this->sequence = is_string($config['sequence']) ? $config['sequence'] : 'lay_sequence';
            $this->link = $this->connection->$schema;
            return true;
        } else {
            return $this->connect();
        }
    }
    /**
     * do database querying
     *
     * @param mixed $sql
     *            SQL或其他查询结构
     * @param string $encoding
     *            编码
     * @param boolean $showsql
     *            是否记录查询信息
     * @return mixed
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
                Logger::info('SET ENCODING ' . $encoding, 'MONGO');
            }
            $connection->encoding = $encoding;
            // mysql_query('SET NAMES ' . $encoding, $link);
            // mysqli_query($link, 'SET NAMES ' . $encoding);
        }
        if($showsql) {
            Logger::info($sql, 'MONGO');
        }
        if($sql) {
            $result = $link->execute($sql);
            // $result = mysql_query($sql, $link);
        }
        
        return $result;
    }
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     * @return array
     */
    public function get($id) {
        // TODO relations
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $coder = new Coder($model, $link);
        $coder->setQuery(array(
                $pk => $id
        ));
        $result = $coder->makeSelectOne();
        return $result;
    }
    /**
     * delete by primary id
     *
     * @param int|string $id
     *            the ID
     * @return boolean
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
        $coder->setQuery(array(
                $pk => $id
        ));
        $result = $coder->makeDelete();
        return $result;
    }
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     * @return boolean int
     */
    public function add(array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $columns = $model->columns();
        $pk = $model->primary();
        $seq = is_a($model, 'lay\core\Increment') ? $model->sequence() : '';
        if(! $link) {
            $this->connect();
        }
        
        $coder = new Coder($model, $link);
        if($seq) {
            $k = array_search($seq, $columns);
            if(! array_key_exists($seq, $info) && $k !== false && ! array_key_exists($columns[$k], $info)) {
                $new = $this->nextSequence();
                $info[$seq] = $new;
            }
        }
        $coder->setValues($info);
        $result = $coder->makeInsert();
        return $result;
    }
    /**
     * update by primary id
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     * @return boolean
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
        $coder->setQuery(array(
                $pk => $id
        ));
        $result = $coder->makeUpdate();
        return $result;
    }
    /**
     * 某些条件下的记录数
     *
     * @param array $info
     *            information array
     * @return int
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
    /**
     * 搜索查询数据
     *
     * @param array $fields
     *            字段数组
     * @param array $query
     *            条件数组
     * @param array $sort
     *            排序数组
     * @param array $limit
     *            limit数组
     * @param array $group
     *            group数组
     * @param array $having
     *            having数组
     */
    public function select($query, $fields, $sort = array(), $limit = array(), $group = array(), $having = array()) {
        return $this->find($query, $fields, $sort, $limit, $group, $having);
    }
    /**
     * 搜索查询数据
     *
     * @param array $fields
     *            字段数组
     * @param array $query
     *            条件数组
     * @param array $sort
     *            排序数组
     * @param array $limit
     *            limit数组
     * @param array $group
     *            group数组
     * @param array $having
     *            having数组
     * @return mixed
     */
    public function find($query, $fields, $sort = array(), $limit = array(), $group = array(), $having = array()) {
        // TODO relations
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $coder = new Coder($model, $link);
        $coder->setFields($fields);
        $coder->setQuery($query);
        $coder->setOrder($sort);
        $coder->setLimit($limit);
        $coder->setGroup($group);
        $coder->setHaving($having);
        $coder->makeSelect();
        return $coder->makeIterator();
    }
    /**
     * find and modify
     */
    /**
     * find and modify
     *
     * @param array $query
     *            条件数组
     * @param array $setter
     *            information array
     * @param array $fields
     *            字段数组
     * @param boolean $new
     *            是否返回新数据
     * @return mixed
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
    /**
     * 返回下一个自增涨数据
     *
     * @param number $step
     *            步阶
     * @return boolean int
     */
    protected function nextSequence($step = 1) {
        Logger::debug('do next');
        if(! is_subclass_of($this->model, 'lay\core\Increment')) {
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
        $seqquery = array(
                'name' => $table . '.' . $seq
        );
        $ret = $seqStore->findModify(array(
                'name' => $table . '.' . $pk
        ), array(
                '$inc' => array(
                        'seq' => $step
                )
        ), array(
                'seq'
        ), true);
        return $ret['seq'];
    }
}
?>

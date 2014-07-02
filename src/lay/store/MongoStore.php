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
     * 数据库连接对象
     *
     * @var MongoClient
     */
    protected $connection;
    /**
     * 标记自增涨字段的数据库表名
     *
     * @var string
     */
    protected $sequence;
    /**
     * 数据库访问对象
     *
     * @var MongoDB
     */
    protected $link;
    /**
     * MongoCollection
     * 
     * @var MongoCollection
     */
    protected $collection;
    /**
     * MongoCursor
     * 
     * @var MongoCursor
     */
    protected $cursor;
    /**
     * Coder
     * 
     * @var Coder
     */
    protected $coder;
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
        
        if(is_a($sql, 'lay\core\Coder')) {
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
        }var_dump($id);
        
        $this->coder = new Coder($model);
        $this->coder->setQuery(array(
                $pk => $id
        ));
        return $this->makeSelectOne();
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
        
        $this->coder = new Coder($model);
        $this->coder->setQuery(array(
                $pk => $id
        ));
        return $this->makeDelete();
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
        
        $this->coder = new Coder($model);
        if($seq) {
            $k = array_search($seq, $columns);
            if(! array_key_exists($seq, $info) && $k !== false && ! array_key_exists($columns[$k], $info)) {
                $new = $this->nextSequence();
                $info[$seq] = $new;
            }
        }
        $this->coder->setValues($info);
        return $this->makeInsert();
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
        
        $this->coder = new Coder($model);
        $this->coder->setSetter($info);
        $this->coder->setQuery(array(
                $pk => $id
        ));
        return $this->makeUpdate();
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
        
        $this->coder = new Coder($model);
        $this->coder->setQuery($info);
        return $this->makeCount();
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
     */
    public function select($query, $fields = array(), $sort = array(), $limit = array(), $group = array(), $having = array()) {
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
    public function find($query, $fields = array(), $sort = array(), $limit = array(), $group = array(), $having = array()) {
        // TODO relations
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $this->coder = new Coder($model);
        $this->coder->setFields($fields);
        $this->coder->setQuery($query);
        $this->coder->setOrder($sort);
        $this->coder->setLimit($limit);
        $this->coder->setGroup($group);
        $this->coder->setHaving($having);
        return $this->makeSelect();
    }
    /**
     * modify
     */
    public function update($query, $setter) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        if(! $link) {
            $this->connect();
        }
        
        $this->coder = new Coder($model);
        $this->coder->setQuery($query);
        $this->coder->setSetter($setter);
        return $this->makeUpdate();
    }
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
    public function findModify($query, $setter, $fields = array(), $new = true) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        if(! $link) {
            $this->connect();
        }
        
        $this->coder = new Coder($model);
        $this->coder->setFields($fields);
        $this->coder->setQuery($query);
        $this->coder->setSetter($setter);
        $this->coder->setNew($new);
        return $this->makeFindModify();
    }
    /**
     * remove
     */
    public function remove($query) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        if(! $link) {
            $this->connect();
        }
        
        $this->coder = new Coder($model);
        $this->coder->setQuery($query);
        return $this->makeDelete();
    }
    /**
     * close connection
     */
    public function close() {
        if($this->link)
            $this->link->close();
    }
    /**
     * 返回单一数据
     *
     * @return mixed
     */
    public function toScalar() {
        $row = array_values($this->toOne(true));
        return $row[0];
    }
    /**
     * 返回单条数据
     *
     * @param boolean $origin
     *            是否数据库原始数据
     * @return mixed
     */
    public function toOne($origin = false) {
        $rows = $this->toArray(1, $origin);
        return $rows[0];
    }
    /**
     * 将结果集转换为指定数量的数组
     *
     * @param int $count
     *            指定数量
     * @param boolean $origin
     *            是否数据库原始数据
     * @return array
     */
    public function toArray($count = 0, $origin = false) {
        $rows = array();
        $result = $this->result;
        $cursor = $this->cursor;
        $model = $this->model;
        if(! $result && ! $cursor) {
            // result is empty or null
        } else {
            $i = 0;
            if($cursor) {
                if(! $result) {
                    $this->doIterator();
                    $result = $this->result;
                }
                foreach($result as $k => $row) {
                    if($count <= 0 || $i < $count) {
                        if($origin) {
                            $rows[$i] = (array)$row;
                        } else {
                            $obj = clone $model;
                            $obj->distinct()->build((array)$row);
                            $rows[$i] = $obj->toArray();
                        }
                        $i++;
                    } else {
                        break;
                    }
                }
            } else if($result && empty($result['err'])) {
                if($origin) {
                    $rows[$i] = (array)$result;
                } else {
                    $m = clone $model;
                    $m->distinct()->build((array)$result);
                    $rows[$i] = $m->toArray();
                }
            }
        }
        return $rows;
    }
    /**
     * 将结果集转换为指定数量的Model对象数组
     *
     * @param int $count
     *            指定数量
     * @return array
     */
    public function toModelArray($count = 0) {
        $rows = array();
        $result = $this->result;
        $cursor = $this->cursor;
        $model = $this->model;
        if(! $result && ! $cursor) {
            // result is empty or null
        } else {
            $i = 0;
            if($cursor) {
                if(! $result) {
                    $this->doIterator();
                    $result = $this->result;
                }
                foreach($result as $k => $row) {
                    if($count <= 0 || $i < $count) {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $obj;
                        $i++;
                    } else {
                        break;
                    }
                }
            } else if($result && empty($result['err'])) {
                $obj = clone $model;
                $obj->distinct()->build((array)$result);
                $rows[$i] = $obj;
            }
        }
        return $rows;
    }
    /**
     * 将结果集转换为指定数量的stdClass对象数组
     *
     * @param int $count
     *            指定数量
     * @return array
     */
    public function toObjectArray($count = 0) {
        $rows = array();
        $result = $this->result;
        $cursor = $this->cursor;
        $model = $this->model;
        if(! $result && ! $cursor) {
            // result is empty or null
        } else {
            $i = 0;
            if($cursor) {
                if(! $result) {
                    $this->doIterator();
                    $result = $this->result;
                }
                foreach($result as $k => $row) {
                    if($count <= 0 || $i < $count) {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $obj->toStdClass();
                        $i++;
                    } else {
                        break;
                    }
                }
            } else if($result && empty($result['err'])) {
                $obj = clone $model;
                $obj->distinct()->build((array)$result);
                $rows[$i] = $obj->toStdClass();
            }
        }
        return $rows;
    }
    /**
     * 返回下一个自增涨数据
     *
     * @param number $step
     *            步阶
     * @return boolean int
     */
    protected function nextSequence($step = 1) {
        if(! is_a($this->model, 'lay\core\Increment')) {
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
        $q = array(
                'name' => $table . '.' . $pk
        );
        $s = array(
                '$inc' => array(
                        'seq' => $step
                )
        );
        $f = array(
                'seq'
        );
        $ret = $seqStore->findModify($q, $s, $f, true);
        return $ret['seq'];
    }
    /**
     * make select query
     * 
     * @return MongoCursor
     */
    private function makeSelect() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindFun();
        $this->makeGroup();
        $this->makeSort();
        $this->makeSkip();
        $this->makeLimit();
        return $this->toArray(0, true);
    }
    /**
     * make select one query
     * 
     * @return mixed
     */
    private function makeSelectOne() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindOneFun();
        return $this->toOne();
    }
    /**
     * make findAndodify query
     * 
     * @return mixed
     */
    private function makeFindModify() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindModifyFun();
        return $this->toOne();
    }
    /**
     * make insert query
     * 
     * @return boolean
     */
    private function makeInsert() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeInsertFun();
        return $this->result ? $this->result['ok'] : false;
    }
    /**
     * make remove querying
     * 
     * @return boolean
     */
    private function makeDelete() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeDeleteFun();
        return $this->result ? $this->result['ok'] : false;
    }
    /**
     * make update query
     * 
     * @return boolean
     */
    private function makeUpdate() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeUpdateFun();
        return $this->result ? $this->result['ok'] : false;
    }
    /**
     * make record's count query
     * 
     * @return int
     */
    private function makeCount() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeCountFun();
        return $this->toScalar();
    }
    /**
     * make record's iterator
     * 
     * @return mixed
     */
    private function doIterator() {
        if(empty($this->cursor)) {
            $this->result = array();
            // don't do
        } else if(is_a($this->cursor, 'MongoCursor')) {
            $this->result = iterator_to_array($this->cursor);
        } else {
            $this->result = $this->cursor['result'];
        }
        return $this->result;
    }
    /**
     * make one record
     * 
     * @return mixed
     */
    private function doOne() {
        if(empty($this->result)) {
            // don't do
            $this->result = false;
        } else {
            // $classname = get_class($this->model);
            // $bean = new $classname();
            // $this->result = $bean->build($this->result)->toArray();
        }
        return $this->result;
    }
    /**
     * make or update MongoDB
     */
    private function makeDb() {
        $this->result = false;
        $this->cursor = false;
        if($this->link) {
            // don't do
        } else if($this->connection && $this->coder->schema) {
            $this->link = $this->connection->selectDB($this->coder->schema);
        } else {
            Logger::error('null given schema or null given mongo client!');
        }
    }
    /**
     * make or update MongoCollection
     */
    private function makeCollection() {
        if($this->link && $this->coder->table) {
            $this->collection = $this->link->selectCollection($this->coder->table);
        } else {
            Logger::error('null given table or null given mongo db!');
        }
    }
    /**
     * make find function
     */
    private function makeFindFun() {
        if($this->collection) {
            if(! $this->coder->fields && $this->model) {
                $this->coder->setFields($this->model->toFields());
            }
            $fields = ! $this->coder->fields ? array() : $this->coder->fields;
            $query = ! $this->coder->query ? array() : $this->coder->query;
            if(method_exists($this->collection, 'aggregate')) {
                $command = array();
                if($query) {
                    $command[]['$match'] = $query;
                }
                if($this->coder->group) {
                    $command[]['$group'] = $this->coder->group;
                }
                if($this->coder->order) {
                    $command[]['$sort'] = $this->coder->order;
                }
                if($fields) {
                    $columns = $this->model->columns();
                    foreach($fields as $field => $v) {
                        $k = array_search($field, $columns);
                        if($k && $field !== $k && $v != 0) {
                            $fields[$field] = 0;
                            $fields[$k] = '$' . $field;
                        }
                    }
                    $command[]['$project'] = $fields;
                }
                if($this->coder->offset > 0) {
                    $command[]['$skip'] = $this->coder->offset;
                }
                if($this->coder->num > 0) {
                    $command[]['$limit'] = $this->coder->num;
                }
                $this->cursor = $this->collection->aggregate($command);
            } else {
                $this->cursor = $this->collection->find($query, $fields);
            }
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make findOne function
     */
    private function makeFindOneFun() {
        $query = ! $this->coder->query ? array() : $this->coder->query;
        $fields = ! $this->coder->fields ? ($this->model ? $this->model->toFields() : array()) : $this->coder->fields;
        if($this->collection) {
            $this->result = $this->collection->findOne($query, $fields);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make findAndModify function
     */
    private function makeFindModifyFun() {
        if(! $this->coder->query || ! $this->coder->setter) {
            // don't do
        } else if($this->collection) {
            if($this->coder->new) {
                $options = array(
                        'new' => true
                );
            } else {
                $options = array();
            }
            $this->result = $this->collection->findAndModify($this->coder->query, $this->coder->setter, $this->coder->fields, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make insert function
     */
    private function makeInsertFun() {
        if(! $this->coder->values) {
            // don't do
        } else if($this->collection) {
            $options = array();
            $this->result = $this->collection->insert($this->coder->values, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make remove function
     */
    private function makeDeleteFun() {
        if(! $this->coder->query) {
            // don't do
        } else if($this->collection) {
            $options = array();
            $this->result = $this->collection->remove($this->coder->query, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make update function
     */
    private function makeUpdateFun() {
        if(! $this->coder->query || ! $this->coder->setter) {
            // don't do
        } else if($this->collection) {
            $options = array('multiple' => 1);
            $this->result = $this->collection->update($this->coder->query, $this->coder->setter, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make count function
     */
    private function makeCountFun() {
        if($this->collection) {
            if(! $this->coder->query) {
                $this->result = $this->collection->count();
            } else {
                $this->result = $this->collection->count($this->coder->query);
            }
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make group function
     */
    private function makeGroup() {
        if(! $this->coder->group) {
            // don't do
        } else if(is_a($this->cursor, 'MongoCursor')) {
            // $this->cursor->($this->order);
        }
    }
    /**
     * make sort function
     */
    private function makeSort() {
        if(! $this->coder->order) {
            // don't do
        } else if(is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->sort($this->order);
        }
    }
    /**
     * make skip function
     */
    private function makeSkip() {
        if($this->coder->offset > 0 && is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->skip($this->coder->offset);
        } else {
            // don't do
        }
    }
    /**
     * make limit function
     */
    private function makeLimit() {
        if($this->coder->num > 0 && is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->limit($this->coder->num);
        } else {
            // don't do
        }
    }
}
?>

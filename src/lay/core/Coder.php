<?php

namespace lay\core;

use Mongo;
use MongoDB;
use MongoClient;
use MongoCollection;
use MongoCursor;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}
/**
 * Mongo Code处理器
 *
 * @author Lay Li
 */
class Coder {
    /**
     * MongoClient,
     * 
     * @var Mongo MongoClient
     */
    private $client;
    /**
     * MongoDB
     * 
     * @var MongoDB
     */
    private $db;
    /**
     * MongoCollection
     * @var MongoCollection
     */
    private $collection;
    /**
     * MongoCursor
     * @var MongoCursor
     */
    private $cursor;
    /**
     * 结果集
     * 
     * @var mixed
     */
    private $result = false;
    /**
     * Model
     * @var Model
     */
    private $model = false;
    /**
     * comand code string
     * @var string
     */
    private $code = '';
    /**
     * current operation flag
     * @var string
     */
    private $operation = 'find';
    /**
     * field array
     * @var array
     */
    private $fields = array();
    /**
     * value array
     * @var array
     */
    private $values = array();
    /**
     * set array
     * @var array
     */
    private $setter = array();
    /**
     * schema string
     * @var string
     */
    private $schema = '';
    /**
     * table string
     * @var string
     */
    private $table = '';
    /**
     * query condition array
     * @var array
     */
    private $query = array();
    /**
     * group condition array
     * @var array
     */
    private $group = array();
    /**
     * having condition array
     * @var array
     */
    private $having = array();
    /**
     * sort condition array
     * @var array
     */
    private $order = array();
    /**
     * skip number
     * @var int
     */
    private $offset = - 1; // for paging
    /**
     * limit number
     * @var int
     */
    private $num = - 1; // for paging
    /**
     * if find new when doing findAndModify
     * @var boolean
     */
    private $new = true;
    /**
     * Query Modifiers
     *
     * @var array
     */
    private $modifiers = array(
            '$comment',
            '$explain',
            '$hint',
            '$maxScan',
            '$maxTimeMS',
            '$max',
            '$min',
            '$orderBy',
            '$returnKey',
            '$showDiskLoc',
            '$snapshot',
            '$query',
            '$natural'
    );
    /**
     * Field Update Operators
     *
     * @var array
     */
    private $sepcifics = array(
            '$inc',
            '$mul',
            '$rename',
            '$setOnInsert',
            '$set',
            '$unset',
            '$min',
            '$max',
            '$currentDate'
    );
    /**
     * Aggregation Framework Operators
     * Pipeline Operators
     *
     * @var array
     */
    private $operators = array(
            '$project', // Reshapes a document stream. $project can rename, add, or remove fields as well as create computed values and sub-documents.
            '$match', // Filters the document stream, and only allows matching documents to pass into the next pipeline stage. $match uses standard MongoDB queries.
            '$redact', // Restricts the content of a returned document on a per-field level.
            '$limit', // Restricts the number of documents in an aggregation pipeline.
            '$skip', // Skips over a specified number of documents from the pipeline and returns the rest.
            '$unwind', // Takes an array of documents and returns them as a stream of documents.
            '$group', // Groups documents together for the purpose of calculating aggregate values based on a collection of documents.
            '$sort', // Takes all input documents and returns them in a stream of sorted documents.
            '$geoNear', // Returns an ordered stream of documents based on proximity to a geospatial point.
            '$out'
    );
    /**
     * 构造方法
     * 
     * @param Model $model Model
     * @param MongoDB $db MongoDB
     * @param MongoClient $client MongoClient
     */
    public function __construct($model = false, $db = false, $client = false) {
        $this->setModel($model);
        $this->setMongoClient($client);
        $this->setMongoDB($db);
    }
    /**
     * 设置model属性
     * @param Model $model Model对象
     */
    public function setModel($model) {
        if(is_subclass_of($model, 'lay\core\Model')) {
            $this->model = $model;
            $this->setTable($model->table());
            $this->setSchema($model->schema());
        }
    }
    /**
     * 设置client属性
     * @param MongoClient $client            
     */
    public function setMongoClient($client) {
        if(is_a($client, 'MongoClient')) {
            $this->client = $client;
        } else if(is_a($client, 'Mongo')) {
            // 支持原Mongo类
            $this->client = $client;
        }
    }
    /**
     * 设置db属性
     * @param MongoDB $db            
     */
    public function setMongoDB($db) {
        if(is_a($db, 'MongoDB')) {
            $this->db = $db;
        }
    }
    /**
     * 设置table属性
     * @param string $table collection name
     */
    public function setTable($table) {
        if(empty($table)) {
            // Logger::error('empty table name');
        } else if(is_string($table)) {
            // 去除可能存在于两边的着重号
            // $table = $this->trimModifier($table);
            $this->table = $table;
        } else {
            Logger::error('invlid table,table name must be string');
        }
    }
    /**
     * 设置schema属性
     * @param string $schema schema name
     */
    public function setSchema($schema) {
        if(empty($schema)) {
            // Logger::error('empty schema');
        } else if(is_string($schema)) {
            // 去除可能存在于两边的着重号
            // $schema = $this->trimModifier($schema);
            $this->schema = $schema;
        } else {
            Logger::error('invlid schema,schema name must be string');
        }
    }
    /**
     *设置fields属性
     * @param array $fields field array
     */
    public function setFields(array $fields) {
        if(empty($fields)) {
            // Logger::error('empty fields');
        } else if(is_array($fields) && $this->model) {
            $tmp = array();
            $columns = $this->model->columns();
            foreach($fields as $field) {
                if(array_search($field, $columns)) {
                    $tmp[$field] = 1;
                } else if(array_key_exists($field, $columns)) {
                    $tmp[$columns[$field]] = 1;
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->fields = $tmp;
        } else if(is_array($fields)) {
            $this->fields = $fields;
        } else if(is_string($fields)) {
            $fields = explode(',', $fields);
            // 去除可能存在于两边的着重号
            $fields = $this->trimModifier($fields);
            $this->setFields($fields);
        } else {
            Logger::error('invalid fields');
        }
    }
    /**
     *设置values属性
     * @param array $values value array
     */
    public function setValues(array $values) {
        if(empty($values)) {
            // Logger::error('empty values');
        } else if(is_array($values) && $this->model) {
            $tmpvalues = array();
            $columns = $this->model->columns();
            foreach($values as $field => $value) {
                if(in_array($field, $this->sepcifics) || array_search($field, $columns)) {
                    $tmpvalues[$field] = $value;
                } else if(array_key_exists($field, $columns)) {
                    $tmpvalues[$columns[$field]] = $value;
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->values = $tmpvalues;
        } else if(is_array($values)) {
            $this->values = $values;
        } else {
            Logger::error('invalid values');
        }
    }
    /**
     * 设置setter属性
     * @param array $info set info array
     */
    public function setSetter(array $info) {
        if(empty($info)) {
            // Logger::error('empty set info');
        } else if(is_array($info) && $this->model) {
            $setter = array();
            $columns = $this->model->columns();
            foreach($info as $field => $value) {
                if(in_array($field, $this->sepcifics)) {
                    $setter[$field] = $value;
                } else if(array_search($field, $columns)) {
                    $setter['$set'][$field] = $value;
                } else if(array_key_exists($field, $columns)) {
                    $setter['$set'][$columns[$field]] = $value;
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->setter = $setter;
        } else if(is_array($info)) {
            $this->setter = $info;
        } else if(is_string($info)) {
            $info = explode(',', $info);
            $info = $this->explodeSetter($info);
            $this->setSetter($info);
        } else {
            Logger::error('invalid set info string or array!');
        }
    }
    /**
     * 设置new属性,
     * if set new when doing findAmdModify
     * 
     * @param boolean $new            
     */
    public function setNew($new = true) {
        $this->new = $new ? true : false;
    }
    /**
     * 设置query属性
     * @param array $info query condition array
     */
    public function setQuery(array $info) {
        if(empty($info)) {
            //
        } else if($this->model) {
            $query = array();
            $columns = $this->model->columns();
            foreach($info as $field => $value) {
                if(array_search($field, $columns)) {
                    $query[$field] = $value;
                } else if(array_key_exists($field, $columns)) {
                    $query[$columns[$field]] = $value;
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->query = $query;
        } else if(is_array($info)) {
            $this->query = $info;
        } else {
            Logger::error('invalid query array!');
        }
    }
    /**
     * 设置order属性
     * @param array $order sorting array
     */
    public function setOrder(array $order) {
        if(empty($order)) {
            // Logger::error('empty info conditions array');
        } else if(is_array($order)) {
            $signs = array(
                    'DESC',
                    'ASC'
            );
            foreach($order as $field => $desc) {
                $desc = strtoupper($desc);
                $desc = in_array($desc, $signs) || $desc == 1 ? ($desc == 'DESC' ? - 1 : 1) : - 1;
                if($this->model) {
                    $columns = $this->model->columns();
                    if(array_search($field, $columns)) {
                        $this->order = array_merge($this->order, array(
                                $field => $desc
                        ));
                    } else if(array_key_exists($field, $columns)) {
                        $this->order = array_merge($this->order, array(
                                $columns[$field] => $desc
                        ));
                    } else {
                        Logger::error('invlid field:' . $field);
                    }
                } else {
                    $this->order = array_merge($this->order, array(
                            $field => $desc
                    ));
                }
            }
        } else {
            Logger::error('invlid info conditions array');
        }
    }
    /**
     * 设置limit
     * @param array $limit skip and limit array
     */
    public function setLimit(array $limit) {
        if(empty($limit)) {
            $this->setOffset(- 1);
            $this->setNum(- 1);
        } else if(is_array($limit) && count($limit) > 1) {
            $offset = ! isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 0 : $limit['offset'];
            $num = ! isset($limit['num']) ? isset($limit['1']) ? $limit['1'] : 20 : $limit['num'];
            $this->setOffset($offset);
            $this->setNum($num);
        } else if(is_array($limit)) {
            $num = ! isset($limit['num']) ? isset($limit['0']) ? $limit['0'] : 20 : $limit['num'];
            $this->setOffset(0);
            $this->setNum($num);
        } else {
            Logger::error('invlid limit array');
        }
    }
    /**
     * 设置offset属性
     * @param int $offset skip number
     */
    public function setOffset($offset) {
        $this->offset = intval($offset);
    }
    /**
     * 设置num属性
     * @param int $num limit number
     */
    public function setNum($num) {
        $this->num = intval($num);
    }
    /**
     * 设置group属性
     * @param array $group group condition array
     */
    public function setGroup(array $group) {
        if(empty($group)) {
            //
        } else {
            // TODO group
        }
    }
    /**
     * 设置having属性
     * @param array $having having condition array
     */
    public function setHaving(array $having) {
        if(empty($having)) {
            //
        } else {
            // TODO having
        }
    }
    /**
     * explode set info array
     * @param array $str info string or array
     * @return array
     */
    private function explodeSetter($str) {
        if(is_array($str)) {
            $setter = array();
            foreach($str as $s) {
                if($set = $this->explodeSetter($s)) {
                    array_merge($setter, $set);
                }
            }
            return $setter;
        } else if(is_string($str)) {
            $setter = explode('=', $str);
            // 去除可能存在于两边的着重号
            $field = $this->trimModifier($setter[0]);
            // 如果两边有单引号则去除掉
            $value = $this->trimQuote($setter[1]);
            return array(
                    $field => $value
            );
        } else {
            Logger::error('invalid set string or array to explode!');
            return false;
        }
    }
    /**
     * make select query
     * @return MongoCursor
     */
    public function makeSelect() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindFun();
        $this->makeGroup();
        $this->makeSort();
        $this->makeSkip();
        $this->makeLimit();
        return $this->cursor;
    }
    /**
     * make select one query
     * @return mixed
     */
    public function makeSelectOne() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindOneFun();
        // $this->makeSort();
        // $this->makeSkip();
        // $this->makeLimit();
        // $this->makeIterator();
        return $this->result;
    }
    /**
     * make findAndodify query
     * @return mixed
     */
    public function makeFindModify() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindModifyFun();
        return $this->result;
    }
    /**
     * make insert query
     * @return boolean
     */
    public function makeInsert() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeInsertFun();
        return $this->result;
    }
    /**
     * make remove querying
     * @return boolean
     */
    public function makeDelete() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeDeleteFun();
        return $this->result;
    }
    /**
     * make update query
     * @return boolean
     */
    public function makeUpdate() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeUpdateFun();
        return $this->result;
    }
    /**
     * make record's count query
     * @return int
     */
    public function makeCount() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeCountFun();
        return $this->result;
    }
    /**
     * make record's iterator
     * @return mixed
     */
    public function makeIterator() {
        $this->result = array();
        if(empty($this->cursor)) {
            // don't do
        } else if(is_subclass_of($this->cursor, 'MongoCursor')) {
            $this->result = iterator_to_array($this->cursor);
        } else {
            $this->result = $this->cursor['result'];
        }
        return $this->result;
    }
    /**
     * make one record
     * @return mixed
     */
    public function makeOne() {
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
        if($this->db) {
            // don't do
        } else if($this->client && $this->schema) {
            $this->db = $this->client->selectDB($this->schema);
        } else {
            Logger::error('null given schema or null given mongo client!');
        }
    }
    /**
     * make or update MongoCollection
     */
    private function makeCollection() {
        if($this->db && $this->table) {
            $this->collection = $this->db->selectCollection($this->table);
        } else {
            Logger::error('null given table or null given mongo db!');
        }
    }
    /**
     * make find function
     */
    private function makeFindFun() {
        if($this->collection) {
            if(empty($this->fields) && $this->model) {
                $this->setFields($this->model->toFields());
            }
            $fields = empty($this->fields) ? array() : $this->fields;
            $query = empty($this->query) ? array() : $this->query;
            if(method_exists($this->collection, 'aggregate')) {
                $command = array();
                if(! empty($query)) {
                    $command[]['$match'] = $query;
                }
                if(! empty($this->group)) {
                    $command[]['$group'] = $this->group;
                }
                if(! empty($this->order)) {
                    $command[]['$sort'] = $this->order;
                }
                if(! empty($fields)) {
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
                if($this->offset > 0) {
                    $command[]['$skip'] = $this->offset;
                }
                if($this->num > 0) {
                    $command[]['$limit'] = $this->num;
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
        $query = empty($this->query) ? array() : $this->query;
        $fields = empty($this->fields) ? ($this->model ? $this->model->toFields() : array()) : $this->fields;
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
        $this->result = false;
        if(empty($this->query) || empty($this->setter)) {
            // don't do
        } else if($this->collection) {
            if($this->new) {
                $options = array(
                        'new' => true
                );
            } else {
                $options = array();
            }
            $this->result = $this->collection->findAndModify($this->query, $this->setter, $this->fields, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make insert function
     */
    private function makeInsertFun() {
        $this->result = false;
        if(empty($this->values)) {
            // don't do
        } else if($this->collection) {
            $options = array();
            $this->result = $this->collection->insert($this->values, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make remove function
     */
    private function makeDeleteFun() {
        $this->result = false;
        if(empty($this->query)) {
            // don't do
        } else if($this->collection) {
            $options = array();
            $this->result = $this->collection->remove($this->query, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make update function
     */
    private function makeUpdateFun() {
        $this->result = false;
        if(empty($this->query) || empty($this->setter)) {
            // don't do
        } else if($this->collection) {
            $options = array();
            $this->result = $this->collection->update($this->query, $this->setter, $options);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make count function
     */
    private function makeCountFun() {
        if($this->collection) {
            if(empty($this->query)) {
                $this->result = $this->collection->count();
            } else {
                $this->result = $this->collection->count($this->query);
            }
        } else {
            Logger::error('null given mongo collection!');
        }
    }
    /**
     * make group function
     */
    private function makeGroup() {
        if(empty($this->group)) {
            // don't do
        } else if(is_a($this->cursor, 'MongoCursor')) {
            // $this->cursor->($this->order);
        }
    }
    /**
     * make sort function
     */
    private function makeSort() {
        if(empty($this->order)) {
            // don't do
        } else if(is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->sort($this->order);
        }
    }
    /**
     * make skip function
     */
    private function makeSkip() {
        if($this->offset > 0 && is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->skip($this->offset);
        } else {
            // don't do
        }
    }
    /**
     * make limit function
     */
    private function makeLimit() {
        if($this->num > 0 && is_a($this->cursor, 'MongoCursor')) {
            $this->cursor->limit($this->num);
        } else {
            // don't do
        }
    }
}
?>

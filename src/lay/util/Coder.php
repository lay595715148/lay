<?php
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
     *
     * @var Mongo
     */
    private $client;
    /**
     *
     * @var MongoDB
     */
    private $db;
    /**
     *
     * @var MongoCollection
     */
    private $collection;
    /**
     *
     * @var MongoCursor
     */
    private $cursor;
    private $result = false;
    /**
     *
     * @var Model
     */
    private $model = false;
    private $modifier = false;
    private $code = '';
    private $operation = 'find';
    private $fields = array();
    private $values = array();
    private $setter = array();
    private $schema = '';
    private $table = '';
    private $query = array();
    private $group = array();
    private $having = array();
    private $order = array();
    private $offset = - 1; // for paging
    private $num = - 1; // for paging
    private $new = true;
    /**
     * Query Modifiers
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
     * @var array
     */
    private $operators = array(
            '$project',//Reshapes a document stream. $project can rename, add, or remove fields as well as create computed values and sub-documents.
            '$match',//Filters the document stream, and only allows matching documents to pass into the next pipeline stage. $match uses standard MongoDB queries.
            '$redact',//Restricts the content of a returned document on a per-field level.
            '$limit',//Restricts the number of documents in an aggregation pipeline.
            '$skip',//Skips over a specified number of documents from the pipeline and returns the rest.
            '$unwind',//Takes an array of documents and returns them as a stream of documents.
            '$group',//Groups documents together for the purpose of calculating aggregate values based on a collection of documents.
            '$sort',//Takes all input documents and returns them in a stream of sorted documents.
            '$geoNear',//Returns an ordered stream of documents based on proximity to a geospatial point.
            '$out'
    );
    public function __construct($model = false, $db = false, $client = false) {
        $this->setModel($model);
        $this->setMongoClient($client);
        $this->setMongoDB($db);
    }
    public function setModel($model) {
        if(is_subclass_of($model, 'Model')) {
            $this->model = $model;
            $this->setTable($model->table());
            $this->setSchema($model->schema());
        }
    }
    public function setMongoClient($client) {
        if(is_a($client, 'MongoClient')) {
            $this->client = $client;
        }
    }
    public function setMongoDB($db) {
        if(is_a($db, 'MongoDB')) {
            $this->db = $db;
        }
    }
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
     * if set new when do find and modify
     */
    public function setNew($new = true) {
        $this->new = $new ? true : false;
    }
    public function setQuery($info) {
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
    public function setOrder($order) {
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
    public function setLimit($limit) {
        if(empty($limit)) {
            $this->setOffset(0);
            $this->setNum(20);
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
    public function setOffset($offset) {
        $this->offset = intval($offset);
    }
    public function setNum($num) {
        $this->num = intval($num);
    }
    public function setGroup() {
    }
    public function setHaving() {
    }
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
     *
     * @return MongoCursor
     */
    public function makeSelect() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindFun();
        $this->makeSort();
        $this->makeSkip();
        $this->makeLimit();
        // $this->makeIterator();
        return $this->cursor;
    }
    public function makeFindModify() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeFindModifyFun();
        return $this->result;
    }
    /**
     *
     * @return boolean
     */
    public function makeInsert() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeInsertFun();
        return $this->result;
    }
    public function makeDelete() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeDeleteFun();
        return $this->result;
    }
    public function makeUpdate() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeUpdateFun();
        return $this->result;
    }
    public function makeCount() {
        $this->makeDb();
        $this->makeCollection();
        $this->makeCountFun();
        return $this->result;
    }
    public function makeIterator() {
        $this->result = false;
        if(empty($this->cursor)) {
            // don't do
        } else {
            $this->result = iterator_to_array($this->cursor);
        }
        return $this->result;
    }
    private function makeDb() {
        if($this->db) {
            // don't do
        } else if($this->client && $this->schema) {
            $this->db = $this->client->selectDB($this->schema);
        } else {
            Logger::error('null given schema or null given mongo client!');
        }
    }
    private function makeCollection() {
        if($this->db && $this->table) {
            $this->collection = $this->db->selectCollection($this->table);
        } else {
            Logger::error('null given table or null given mongo db!');
        }
    }
    private function makeFindFun() {
        $query = empty($this->query) ? array() : $this->query;
        $fields = empty($this->fields) ? ($this->model ? $this->model->toFields() : array()) : $this->fields;
        if($this->collection) {
            $this->cursor = $this->collection->find($query, $fields);
        } else {
            Logger::error('null given mongo collection!');
        }
    }
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
    private function makeSort() {
        if(empty($this->order)) {
            // don't do
        } else {
            $this->cursor->sort($this->order);
        }
    }
    private function makeSkip() {
        if($this->offset > 0) {
            $this->cursor->skip($this->offset);
        } else {
            // don't do
        }
    }
    private function makeLimit() {
        if($this->num > 0) {
            $this->cursor->limit($this->num);
        } else {
            // don't do
        }
    }
    
    /**
     * make select sql
     *
     * @return string
     */
    public function makeSelectCode() {
        $this->code = 'db.#replace#';
        $this->makeCollectionCode();
        $this->makeQueryCode();
        $this->makeFieldsCode();
        
        $this->makeSortCode();
        $this->makeSkipCode();
        $this->makeLimitCode();
        // $this->makeBatchCode();
        // $this->makeOptionCode();
        return $this->code;
    }
    private function makeCollectionCode() {
        $table = $this->table;
        $this->code = str_replace('#replace#', $table . '.#replace#', $this->code);
    }
    private function makeFindCode() {
        $this->code = str_replace('#replace#', 'find(#replace#)', $this->code); // $this->operation = 'find(#replace#)';
    }
    private function makeQueryCode() {
        $query = self::array2Code($this->query);
        $this->code = str_replace('#replace#', $query . ', #replace#', $this->code);
    }
    private function makeFieldsCode() {
        $fields = self::array2Code($this->fields);
        $this->code = str_replace('#replace#', $fields, $this->code);
    }
    private function makeSortCode() {
        $sort = self::array2Code($this->sort);
        $this->code .= '.sort(' . $this->sort . ')';
    }
    private function makeSkipCode() {
        $this->code .= '.skip(' . $this->skip . ')';
    }
    private function makeLimitCode() {
        $this->code .= '.limit(' . $this->limit . ')';
    }
    /**
     *
     * @param array $arr            
     * @param string $flag
     *            if return converted string or array
     * @return string array
     */
    public static function array2Code($arr, $flag = true) {
        $tmp = array();
        foreach($arr as $k => $v) {
            if(is_int($k) && is_string($v)) {
                $tmp[$v] = 1;
            } else if(is_int($k)) {
                Logger::error('unsupported value convert to code');
            } else if(is_string($k)) {
                if(is_array($var) || is_object($var)) {
                    $tmp[$k] = self::array2Code($arr, false);
                } else {
                    $tmp[$k] = $v;
                }
            }
        }
        return $flag ? json_encode($tmp) : $tmp;
    }
}
?>

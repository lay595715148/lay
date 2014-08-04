<?php

/**
 * 操作mysql数据库类
 * 
 * @author Lay Li
 */
namespace lay\store;

use lay\App;
use lay\core\Connection;
use lay\core\Criteria;
use lay\core\Store;
use lay\util\Logger;
use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 操作mysql数据库类
 *
 * @author Lay Li
 */
class MysqlStore extends Store {
    /**
     * mysql数据库连接句柄
     *
     * @var Connection
     */
    protected $connection;
    /**
     * 构造方法
     *
     * @param Model $model
     *            模型对象
     * @param string $name
     *            名称
     */
    public function __construct($model, $name = 'default') {
        if(is_string($name)) {
            $config = App::get('stores.' . $name);
        } else if(is_array($name)) {
            $config = $name;
        }
        parent::__construct($name, $model, $config);
    }
    /**
     * 执行连接mysql数据库
     */
    public function connect() {
        try {
            $this->connection = Connection::mysql($this->name, $this->config);
            $this->link = $this->connection->link;
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString(), 'MYSQL');
            return false;
        }
        return mysqli_select_db($this->link, $this->schema);
    }
    /**
     * 切换mysql数据库
     *
     * @param string $name
     *            名称
     * @return boolean
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
            $this->connection = Connection::mysql($name, $config);
            $this->link = $this->connection->link;
            // return mysql_select_db($schema, $this->link);
            return mysqli_select_db($this->link, $schema);
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
     */
    public function query($sql, $encoding = 'UTF8', $showsql = false) {
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
                Logger::info('SET NAMES ' . $encoding, 'MYSQL');
            }
            $connection->encoding = $encoding;
            mysqli_query($link, 'SET NAMES ' . $encoding);
        }
        if($showsql) {
            Logger::info($sql, 'MYSQL');
        }
        if($sql) {
            $result = false;
            $result = mysqli_query($link, $sql);
        }
        
        return $result;
    }
    /**
     * select by primary id
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
        
        $criteria = new Criteria($model);
        $criteria->addCondition($pk, $id);
        $sql = $criteria->makeSelectSQL();
        $this->query($sql, 'UTF8', true);
        
        return $this->toOne(1);
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
        
        $criteria = new Criteria($model);
        $criteria->setCondition(array(
                $pk,
                $id
        ));
        $sql = $criteria->makeDeleteSQL();
        
        return $this->query($sql, 'UTF8', true);
    }
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     * @return int
     */
    public function add(array $info) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        $columns = $model->columns();
        if(! $link) {
            $this->connect();
        }
        if(empty($info)) {
            return false;
        }
        
        $criteria = new Criteria($model);
        $criteria->setValues($info);
        $sql = $criteria->makeInsertSQL();
        
        $result = $this->query($sql, 'UTF8', true);
        return $result ? $this->toLastid() : false;
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
        if(empty($info)) {
            return false;
        }
        
        $criteria = new Criteria($model);
        $criteria->setSetter($info);
        $criteria->setCondition(array(
                $pk,
                $id
        ));
        $sql = $criteria->makeUpdateSQL();
        
        return $this->query($sql, 'UTF8', true);
    }
    /**
     * 条件下记录数
     *
     * @param array $info
     *            query information array
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
        
        $criteria = new Criteria($model);
        $criteria->addMultiCondition($info);
        $sql = $criteria->makeCountSQL();
        
        $result = $this->query($sql, 'UTF8', true);
        return $this->toScalar();
    }
    /**
     * 搜索查询数据
     *
     * @param array $fields
     *            字段数组
     * @param array $condition
     *            条件数组
     * @param array $order
     *            排序数组
     * @param array $limit
     *            limit数组
     * @param array $group
     *            group数组
     * @param array $having
     *            having数组
     * @return array
     */
    public function select($fields = array(), $condition = array(), $order = array(), $limit = array(), $group = array(), $having = array()) {
        // TODO relations
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(! $link) {
            $this->connect();
        }
        
        $criteria = new Criteria($model);
        $criteria->addMultiCondition($condition);
        $criteria->setOrder($order);
        $criteria->setLimit($limit);
        $criteria->setGroup($group);
        $criteria->setHaving($having);
        $sql = $criteria->makeSelectSQL();
        
        $result = $this->query($sql, 'UTF8', true);
        return $this->toArray();
    }
    
    /**
     * 获取结果集中的行数或执行影响的行数
     *
     * @param bool $isselect
     *            是不是SELECT语句的记录数，否则是上一条SQL语句的影响记录数
     * @return mixed
     */
    public function toCount($isselect = true) {
        if($isselect) {
            return mysqli_num_rows($result);
        } else {
            return mysql_affected_rows($this->link);
        }
    }
    /**
     * return mysql last insert id
     *
     * @return
     *
     *
     *
     *
     */
    public function toLastid() {
        return mysqli_insert_id($this->link);
    }
    /**
     * 返回单一数据
     *
     * @return mixed
     */
    public function toScalar() {
        $row = mysqli_fetch_row($this->result);
        return $row['0'];
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
     * @return array
     */
    public function toArray($count = 0, $origin = false) {
        $rows = array();
        $result = $this->result;
        $model = $this->model;
        if(! $result) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    if($origin) {
                        $rows[$i] = (array)$row;
                    } else {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $m->toArray();
                    }
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $rows[$i] = (array)$row;
                    $i++;
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
        $model = $this->model;
        if(! $result) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj;
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj;
                    $i++;
                }
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
        $model = $this->model;
        if(! $result) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj->toStdClass();
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj->toStdClass();
                    $i++;
                }
            }
        }
        
        return $rows;
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

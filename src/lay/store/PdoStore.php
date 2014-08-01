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
use PDO;
use PDOStatement;
use lay\core\Statment;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * PDO操作数据库类
 *
 * @author Lay Li
 */
class PdoStore extends Store {
    /**
     * mysql数据库连接句柄
     *
     * @var Connection
     */
    protected $connection;
    /**
     * PDO
     * @var PDO
     */
    protected $link;
    /**
     * PDOStatement
     * @var PDOStatement
     */
    protected $stmt;
    /**
     * bound param value array
     * @var array
     */
    protected $paramValues;
    /**
     * 构造方法
     *
     * @param Model $model
     *            模型对象
     * @param string $name
     *            名称
     */
    public function __construct($model, $name = 'pdo') {
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
            $name = $this->name;
            $config = $this->config;
            $this->connection = Connection::pdo($name, $config);
            $this->link = $this->connection->link;
            if(!empty($config['encoding'])) {
                $this->connection->encoding = $config['encoding'];
                $sql = 'SET NAMES ' . $config['encoding'];
                if(!empty($config['showsql'])) {
                    Logger::info($sql, 'PDO');
                }
                $this->link->exec($sql);
            }
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString(), 'PDO');
            return false;
        }
        return true;
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
            $this->connection = Connection::pdo($name, $config);
            $this->link = $this->connection->link;
            if(!empty($config['encoding'])) {
                $sql = 'SET NAMES ' . $config['encoding'];
                if(!empty($config['showsql'])) {
                    Logger::info($sql, 'PDO');
                }
                $this->link->exec($sql);
            }
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
     */
    public function query($sql, $encoding = '', $showsql = false) {
        $config = &$this->config;
        $result = &$this->result;
        $model = &$this->model;
        $connection = &$this->connection;
        $link = &$this->link;
        $stmt = &$this->stmt;
        if(empty($link)) {
            $this->connect();
        }

        if(! $showsql && $config['showsql']) {
            $showsql = $config['showsql'];
        }
        if($showsql) {
            Logger::info($sql, 'PDO');
            Logger::info($this->paramValues, 'PDO');
        }
        if($sql) {
            if(strpos(strtolower(trim($sql)), 'select') === 0) {
                if(empty($this->paramValues)) {
                    $stmt = $this->link->query($sql);
                } else {
                    $stmt = $this->link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                    $stmt->execute($this->paramValues);
                }
                $stmt->setFetchMode(PDO::FETCH_CLASS, get_class($model));
                $result = $stmt->fetchAll();
            } else {
                $result = $this->link->exec($sql);
            }
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
        
        $criteria = new Statment($model);
        $criteria->addCondition($pk, $id);
        $sql = $criteria->makeSelectSQL();
        $this->paramValues = $criteria->returnParamValues();
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
        
        $criteria = new Statment($model);
        $criteria->setCondition(array(
                $pk,
                $id
        ));
        $sql = $criteria->makeDeleteSQL();
        $this->paramValues = $criteria->returnParamValues();
        
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
        
        $criteria = new Statment($model);
        $criteria->setValues($info);
        $sql = $criteria->makeInsertSQL();
        $this->paramValues = $criteria->returnParamValues();
        
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
        
        $criteria = new Statment($model);
        $criteria->setSetter($info);
        $criteria->setCondition(array(
                $pk,
                $id
        ));
        $sql = $criteria->makeUpdateSQL();
        $this->paramValues = $criteria->returnParamValues();
        
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
        
        $criteria = new Statment($model);
        $criteria->addMultiCondition($info);
        $sql = $criteria->makeCountSQL();
        $this->paramValues = $criteria->returnParamValues();
        
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
        
        $criteria = new Statment($model);
        $criteria->addMultiCondition($condition);
        $criteria->setOrder($order);
        $criteria->setLimit($limit);
        $criteria->setGroup($group);
        $criteria->setHaving($having);
        $sql = $criteria->makeSelectSQL();
        $this->paramValues = $criteria->returnParamValues();
        
        $result = $this->query($sql, 'UTF8', true);
        return $this->toModelArray();
    }
    
    /**
     * 获取结果集中的行数或执行影响的行数
     *
     * @param bool $isselect
     *            是不是SELECT语句的记录数，否则是上一条SQL语句的影响记录数
     * @return mixed
     */
    public function toCount($isselect = true) {
        if($this->stmt) {
            return $this->stmt->rowCount();
        } else {
            if($isselect) {
                return count($this->result);
            }
            return is_numeric($this->result) ? $this->result : 0;
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
        return $this->link->lastInsertId();
    }
    /**
     * 返回单一数据
     *
     * @return mixed
     */
    public function toScalar() {
        $row = array_values($this->toOne());
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
        return empty($rows[0]) ? array() : $rows[0];
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
        $result = &$this->result;
        $model = &$this->model;
        if(empty($result)) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            foreach ($result as $k => $row) {
                if($i < $count) {
                    if(is_a($row, get_class($model))) {
                        if($origin) {
                            $rows[$i] = $row->toArray();
                        } else {
                            $rows[$i] = $row;
                        }
                    } else {
                        if($origin) {
                            $rows[$i] = (array)$row;
                        } else {
                            $obj = clone $model;
                            $obj->distinct()->build((array)$row);
                            $rows[$i] = $obj->toArray();
                        }
                    }
                    $i++;
                } else {
                    break;
                }
            }
        } else {
            $i = 0;
            foreach ($result as $k => $row) {
                if(is_a($row, get_class($model))) {
                    if($origin) {
                        $rows[$i] = $row->toArray();
                    } else {
                        $rows[$i] = $row;
                    }
                } else {
                    if($origin) {
                        $rows[$i] = (array)$row;
                    } else {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $obj->toArray();
                    }
                }
                $i++;
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
        $result = &$this->result;
        $model = &$this->model;
        if(empty($result)) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            foreach ($result as $k => $row) {
                if($i < $count) {
                    if(is_a($row, get_class($model))) {
                        $rows[$i] = $row;
                    } else {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $obj;
                    }
                    $i++;
                } else {
                    break;
                }
            }
        } else {
            $i = 0;
            foreach ($result as $k => $row) {
                if(is_a($row, get_class($model))) {
                    $rows[$i] = $row;
                } else {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj;
                }
                $i++;
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
        $result = &$this->result;
        $model = &$this->model;
        if(! $result) {
            // result is empty or null
        } else if($count > 0) {
            $i = 0;
            foreach ($result as $k => $row) {
                if($i < $count) {
                    if(is_a($row, get_class($model))) {
                        $rows[$i] = $row->toStdClass();
                    } else {
                        $obj = clone $model;
                        $obj->distinct()->build((array)$row);
                        $rows[$i] = $obj->toStdClass();
                    }
                    $i++;
                }
            }
        } else {
            $i = 0;
            foreach ($result as $k => $row) {
                if(is_a($row, get_class($model))) {
                    $rows[$i] = $row->toStdClass();
                } else {
                    $obj = clone $model;
                    $obj->distinct()->build((array)$row);
                    $rows[$i] = $obj->toStdClass();
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

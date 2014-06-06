<?php
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
class MysqlStore extends Store {
    /**
     *
     * @var Connection
     */
    private $connection;
    public function __construct($model, $name = 'default') {
        if(is_string($name)) {
            $config = App::get('stores.' . $name);
        } else if(is_array($name)) {
            $config = $name;
        }
        parent::__construct($name, $model, $config);
    }
    /**
     * 连接Mysql数据库
     */
    public function connect() {
        try {
            $this->connection = Connection::mysql($this->name, $this->config);
            $this->link = $this->connection->connection;
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString(), 'MYSQL');
            return false;
        }
        return mysqli_select_db($this->link, $this->schema);
    }
    /**
     * 切换Mysql数据库
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
            $this->link = $this->connection->connection;
            //return mysql_select_db($schema, $this->link);
            return mysqli_select_db($this->link, $schema);
        } else {
            return $this->connect();
        }
    }
    /**
     * do database querying
     *
     * @param fixed $sql
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
            //mysql_query('SET NAMES ' . $encoding, $link);
            mysqli_query($link, 'SET NAMES ' . $encoding);
        }
        if($showsql) {
            Logger::info($sql, 'MYSQL');
        }
        if($sql) {
            $result = mysqli_query($link, $sql);
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
        
        $criteria = new Criteria($model);
        $criteria->addCondition($pk, $id);
        $sql = $criteria->makeSelectSQL();
        $this->query($sql, 'UTF8', true);
        
        return $this->toArray(1);
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
    public function select($fields = array(), $condition = array(), $order = array(), $limit = array(), $group = array(), $having = array()) {
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
        return $this->toModel();
    }
    
    /**
     * 获取结果集中的行数或执行影响的行数
     * 
     * @param mixed $result            
     * @param bool $isselect            
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
     * return id
     * 
     * @return
     *
     */
    public function toLastid() {
        return mysqli_insert_id($this->link);
    }
    /**
     * return SCALAR
     * 
     * @return
     *
     */
    public function toScalar() {
        $row = mysqli_fetch_row($this->result);
        return $row['0'];
    }
    /**
     * 将结果集转换为指定数量的数组
     * 
     * @param int $count            
     * @param mixed $result            
     * @return array
     */
    public function toArray($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(! $result) {
            // TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj->toArray();
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj->toArray();
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
     * @param mixed $result            
     * @return array
     */
    public function toModel($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(! $result) {
            // TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj;
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj;
                    $i++;
                }
            }
        }
        
        return $rows;
    }
    /**
     * 将结果集转换为指定数量的基本对象数组
     * 
     * @param int $count            
     * @param mixed $result            
     * @return array
     */
    public function toObject($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(! $result) {
            // TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($i < $count && $row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj->toObject();
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(mysqli_num_rows($result)) {
                while($row = mysqli_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build(( array )$row);
                    $rows[$i] = $obj->toObject();
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

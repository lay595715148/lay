<?php
if(! defined('INIT_LAY')) {
    exit();
}
class Mysql extends Store {
    protected $encoding;
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
            $this->link = Connection::mysql($this->name, $this->config)->connection;
        } catch (Exception $e) {
            Logger::error($e->getTraceAsString());
            return false;
        }
        return mysql_select_db($this->schema, $this->link);
    }
    /**
     * 切换Mysql数据库
     *
     * @param string $name
     *            名称
     */
    public function change($name = '') {
        if($name) {
            $config = App::getStoreConfig($name);
            $schema = isset($config['schema']) && is_string($config['schema']) ? $config['schema'] : '';
            $this->encoding = false;
            $this->link = Connection::mysql($name, $config)->connection;
            return mysql_select_db($schema, $this->link);
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
        $link   = &$this->link;
        if(!$link) { $this->connect(); }
        
        if(!$encoding && $config['encoding']) {
            $encoding = $config['encoding'];
        }
        if(!$showsql && $config['showsql']) {
            $showsql = $config['showsql'];
        }
        if($encoding && $this->encoding != $encoding) {
            if($showsql) {
                Logger::info('SET NAMES '.$encoding, 'Mysql');
            }
            $this->encoding = $encoding;
            mysql_query('SET NAMES '.$encoding, $link);
        }
        if($showsql) {
            Logger::info($sql, 'Mysql');
        }
        if($sql) {
            $result = mysql_query($sql, $link);
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
        if(!$link) { $this->connect(); }
        
        
        $sql = "SELECT * FROM `{$this->schema}`.`{$table}` WHERE `{$pk}` = '{$id}'";
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
        if(!$link) { $this->connect(); }
        
        $sql = "DELETE FROM `{$this->schema}`.`{$table}` WHERE `{$pk}` = '{$id}'";
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
        if(!$link) { $this->connect(); }
        if(empty($info)) { return false; }
        
        $into = array();
        if($pk) {
            $fields = array_diff($model->toFields(), array($pk));
        } else {
            $fields = $model->toFields();
        }
        foreach ($fields as $field) {
            $pro = $model->toProperty($field);
            if(array_key_exists($field, $info)) {
                $into[$field] = mysql_escape_string($info[$field]);
            } else if(array_key_exists($pro, $info)) {
                $into[$field] = mysql_escape_string($info[$pro]);
            } else {
                $into[$field] = $model->{$pro};
            }
        }
        $fstr = implode("`, `", array_keys($into));
        $vstr = implode("', '", array_values($into));
        
        $sql = "INSERT INTO `{$this->schema}`.`{$table}`(`{$fstr}`) VALUES('{$vstr}')";
        
        return $this->query($sql, 'UTF8', true);
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
        if(!$link) { $this->connect(); }
        if(empty($info)) { return false; }

        $into = array();
        if($pk) {
            $fields = array_diff($model->toFields(), array($pk));
        } else {
            $fields = $model->toFields();
        }
        foreach ($fields as $field) {
            $pro = $model->toProperty($field);
            if(array_key_exists($field, $info)) {
                $val = mysql_escape_string($info[$field]);
                $into[] = "`{$field}` = '{$val}'";
            } else if(array_key_exists($pro, $info)) {
                $val = mysql_escape_string($info[$pro]);
                $into[] = "`{$field}` = '{$val}'";
            }
        }
        $setstr = implode(", ", array_values($into));
        
        $sql = "UPDATE `{$this->schema}`.`{$table}` SET {$setstr} WHERE `{$pk}` = '{$id}'";
        
        return $this->query($sql, 'UTF8', true);
    }
    public function count(array $info = array()) {
        $result = &$this->result;
        $link = &$this->link;
        $model = &$this->model;
        $table = $model->table();
        $pk = $model->primary();
        if(!$link) { $this->connect(); }

        if(empty($info)) {
            $sql = "SELECT COUNT(*) FROM `{$this->schema}`.`{$table}`";
        } else {
            $into = array();
            if($pk) {
                $fields = array_diff($model->toFields(), array($pk));
            } else {
                $fields = $model->toFields();
            }
            foreach ($fields as $field) {
                $pro = $model->toProperty($field);
                if(array_key_exists($field, $info)) {
                    $val = mysql_escape_string($info[$field]);
                    $into[] = "`{$field}` = '{$val}'";
                } else if(array_key_exists($pro, $info)) {
                    $val = mysql_escape_string($info[$pro]);
                    $into[] = "`{$field}` = '{$val}'";
                }
            }
            $setstr = implode(" AND ", array_values($into));
            $sql = "SELECT COUNT(*) AS count FROM `{$this->schema}`.`{$table}` WHERE {$setstr}";
        }
        $result = $this->query($sql, 'UTF8', true);
        return $this->toScalar();
    }
    /**
     * 获取结果集中的行数或执行影响的行数
     * @param mixed $result
     * @param bool $isselect
     * @return mixed
     */
    public function toCount($isselect = true) {
        if($isselect) {
            return mysql_num_rows($result);
        } else {
            return mysql_affected_rows($this->link);
        }
    }
    /**
     * return SCALAR
     * @return 
     */
    public function toScalar() {
        $row = mysql_fetch_row($this->result);
        return $row['0'];
    }
    /**
     * 将结果集转换为指定数量的数组
     * @param int $count
     * @param mixed $result
     * @return array
     */
    public function toArray($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(!$result) {
            //TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($i < $count && $row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
                    $rows[$i] = $obj->toArray();
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
                    $rows[$i] = $obj->toArray();
                    $i++;
                }
            }
        }
        
        return $rows;
    }
    /**
     * 将结果集转换为指定数量的Model对象数组
     * @param int $count
     * @param mixed $result
     * @return array
     */
    public function toModel($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(!$result) {
            //TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($i < $count && $row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
                    $rows[$i] = $obj;
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
                    $rows[$i] = $obj;
                    $i++;
                }
            }
        }
        
        return $rows;
    }
    /**
     * 将结果集转换为指定数量的基本对象数组
     * @param int $count
     * @param mixed $result
     * @return array
     */
    public function toObject($count = 0) {
        $rows = array();
        $result = $this->result;
        $classname = get_class($this->model);
        if(!$result) {
            //TODO result is empty or null
        } else if($count != 0) {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($i < $count && $row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
                    $rows[$i] = $obj->toObject();
                    $i++;
                }
            }
        } else {
            $i = 0;
            if(@mysql_num_rows($result)) {
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                    $obj = new $classname();
                    $obj->build((array)$row);
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

<?php
if(! defined('INIT_LAY')) {
    exit();
}
/**
 * SQL处理器
 * @author Lay Li
 */
class Criteria {
    /**
     *
     * @var Model
     */
    private $model = false;
    private $operation = 'SELECT';
    private $fields = '';
    private $values = '';
    private $setter = '';
    private $schema = '';
    private $table = '';
    private $condition = '';
    private $group = '';
    private $having = '';
    private $order = '';
    private $offset = - 1; // for paging
    private $num = - 1; // for paging
    private $sql = '';
    
    /**
     * please always set model
     *
     * @param Model $model            
     */
    public function __construct($model = false) {
        if(is_subclass_of($model, 'Model')) {
            $this->model = $model;
            $this->setTable($model->table());
            $this->setSchema($model->schema());
        }
    }
    public function setModel($model) {
        if(is_subclass_of($model, 'Model')) {
            $this->model = $model;
            $this->setTable($model->table());
            $this->setSchema($model->schema());
        }
    }
    /**
     * 设置SQL FIELDS部分
     *
     * @param array $fields            
     */
    public function setFields(array $fields) {
        if(empty($fields)) {
            //Logger::error('empty fields');
        } else if(is_array($fields) && $this->model) {
            $tmp = array();
            $fields = array_map('trim', $fields);
            $columns = $this->model->columns();
            foreach($fields as $field) {
                if(array_search($field, $columns)) {
                    $tmp[] = $field;
                } else if(array_key_exists($field, $columns)) {
                    $tmp[] = $columns[$field];
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->fields = '`' . implode('`, `', $tmp) . '`';
        } else if(is_array($fields)) {
            $fields = array_map('trim', $fields);
            $this->fields = '`' . implode('`, `', $fields) . '`';
        } else if(is_string($fields)) {
            $fields = str_replace('`', '', $fields);
            $fields = explode(',', $fields);
            $this->setFields($fields);
        } else {
            Logger::error('invalid fields');
        }
    }
    /**
     * 设置INTO中的VALUES部分，同时也将INTO中FIELDS部分设置了
     * 注：传入参数不支持string类型
     *
     * @param array $values            
     */
    public function setValues(array $values) {
        if(empty($values)) {
            //Logger::error('empty values');
        } else if(is_array($values) && $this->model) {
            $tmpfields = array();
            $tmpvalues = array();
            $columns = $this->model->columns();
            foreach($values as $field => $value) {
                if(array_search($field, $columns)) {
                    $tmpfields[] = $field;
                    $tmpvalues[] = mysql_escape_string($value);
                } else if(array_key_exists($field, $columns)) {
                    $tmpfields[] = $columns[$field];
                    $tmpvalues[] = mysql_escape_string($value);
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->fields = ! empty($tmpfields) ? '`' . implode('`, `', $tmpfields) . '`' : '';
            $this->values = ! empty($tmpvalues) ? '\'' . implode('\', \'', $tmpvalues) . '\'' : '';
        } else if(is_array($values)) {
            $this->fields = '`' . implode('`, `', array_keys($values)) . '`';
            $this->values = '\'' . implode('\', \'', array_map('mysql_escape_string', array_values($values))) . '\'';
        } else {
            Logger::error('invalid values');
        }
    }
    /**
     * 设置SQL SET部分
     *
     * @param array $info            
     */
    public function setSetter(array $info) {
        if(empty($info)) {
            //Logger::error('empty set info');
        } else if(is_array($info) && $this->model) {
            $setter = array();
            $columns = $this->model->columns();
            foreach($info as $field => $v) {
                $val = mysql_escape_string($v);
                if(array_search($field, $columns)) {
                    $setter[] = "`{$field}` = '{$val}'";
                } else if(array_key_exists($field, $columns)) {
                    $setter[] = "`{$columns[$field]}` = '{$val}'";
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->setter = implode(', ', $setter);
        } else if(is_array($info)) {
            $setter = array();
            foreach($info as $f => $v) {
                $f = trim($f, ' `');
                $field = mysql_escape_string($f);
                $val = mysql_escape_string($v);
                $setter[] = "`{$field}` = '{$val}'";
            }
            $this->setter = implode(', ', $setter);
        } else if(is_string($info)) {
            $info = explode(',', $fields);
            $info = array_map('trim', $info);
            $this->explodeSetter($info);
        } else {
            Logger::error('invalid set info string or array!');
        }
    }
    public function setTable($table) {
        if(empty($table)) {
            //Logger::error('empty table name');
        } else if(is_array($table) && $this->model) {
            // end is table name, schema maybe exists
            $tablename = trim(end($table), ' `');
            $schema = trim(array_shift($tablearr), ' `');
            if($tablename == $this->model->table()) {
                $this->table = '`' . trim($tablename, ' `') . '`';
                if($schema) {
                    $this->schema = '`' . trim($schema, ' `') . '`';
                }
            } else {
                Logger::error('invalid table name');
            }
        } else if(is_array($table)) {
            // end is table name, fisrt is schema,schema maybe exists
            $tablename = trim(end($table), ' `');
            $schema = trim(array_shift($table), ' `');
            $this->table = '`' . trim($tablename, ' `') . '`';
            if($schema) {
                $this->schema = '`' . trim($schema, ' `') . '`';
            }
        } else if(is_string($table)) {
            $tablearr = array_slice(explode('.', $table), 0, 2);
            $this->setTable($tablearr);
        } else {
            Logger::error('invlid table,table must be string or array');
        }
    }
    public function setSchema($schema) {
        if(empty($schema)) {
            //Logger::error('empty schema');
        } else if(is_string($schema)) {
            $this->schema = '`' . trim($schema, ' `') . '`';
        } else {
            Logger::error('invlid schema,schema must be string');
        }
    }
    public function setCondition($condition) {
        if(empty($condition)) {
            //Logger::error('empty condition');
        } else if(is_array($condition)) {
            $field = $condition[0];
            $value = $condition[1];
            $symbol = $condition[2] ? $condition[2] : '=';
            $combine = $condition[3] ? $condition[3] : 'AND';
            $options = is_array($condition[4]) ? $condition[3] : array();
            $this->addCondition($field, $value, $symbol, $combine, $options);
        } else {
            Logger::error('invlid condition');
        }
    }
    public function addConditions($conditions) {
        if(empty($conditions)) {
            //Logger::error('empty conditions array');
        } else if(is_array($conditions)) {
            foreach($conditions as $condition) {
                $this->setCondition($condition);
            }
        } else {
            Logger::error('invlid conditions array');
        }
    }
    public function addInfoCondition($info) {
        if(empty($info)) {
            //Logger::error('empty info conditions array');
        } else if(is_array($info)) {
            foreach($info as $field => $value) {
                $this->addCondition($field, $value);
            }
        } else {
            Logger::error('invlid info conditions array');
        }
    }
    public function addMultiCondition($mix) {
        if(empty($mix)) {
            //Logger::error('empty info conditions array');
        } else if(is_array($mix)) {
            foreach($mix as $field => $value) {
                if(!is_numeric($field)) {
                    $this->addCondition($field, $value);
                } else if(is_array($value)){
                    $this->setCondition($value);
                } else {
                    $this->setCondition($mix);
                    break;
                }
            }
        } else {
            Logger::error('invlid info conditions array');
        }
    }
    public function addCondition($field, $value, $symbol = '=', $combine = 'AND', $options = array()) {
        if(empty($field)) {
            //Logger::error('empty condition field,or empty condition value');
        } else if(is_string($field)) {
            $combines = array(
                    'AND',
                    'OR'
            );
            $combine = strtoupper($combine);
            if(! in_array($combine, $combines)) {
                $combine = 'AND';
            }
            $this->condition .= $this->condition ? ' ' . $combine . ' ' : '';
            
            if($this->model){
                $table = $this->model->table();
                $this->setTable($table);//拆分出真实的表名
                $columns = $this->model->columns();
                //option中存在table参数，一般使用不到，可调节优等级
                $fieldstr = $options['table'] ? '`' . trim($this->table, ' `') . '`' . '.' : '';
                if(array_search($field, $columns)) {
                    $fieldstr .= '`' . trim($field, ' `') . '`';
                    $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
                } else if(array_key_exists($field, $columns)) {
                    //if field is a property
                    $field = $columns[$field];
                    $fieldstr .= '`' . trim($field, ' `') . '`';
                    $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
                } else {
                    Logger::error('invlid condition field');
                }
            } else {
                $fieldstr = '`' . trim($field, ' `') . '`';
                $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
            }
        } else {
            Logger::error('invlid condition field');
        }
    }
    private function switchSymbolCondition($symbol, $fieldstr, $value, $options = array()) {
        $condition = '';
        $symbol = strtolower($symbol);//变成小写
        switch($symbol) {
            case '>':
            case '<':
            case '>=':
            case '<=':
            case '<>':
            case '!=':
            case '=':
                $value = mysql_escape_string($value);
                $condition = $fieldstr . ' '.$symbol.' \'' . $value . '\'';
                break;
            case 'in':
            case '!in':
            case 'unin':
                $tmp = $symbol == 'in' ? 'IN' : 'NOT IN';
                if(is_string($value)) {
                    //去除可能存在于两边的单引号
                    $value = preg_replace('/^\'(.*)\'$/', '$1', explode(',', $value));
                    $value = array_map('mysql_escape_string', $value);
                    $condition = $fieldstr . ' '.$tmp.' (\'' . implode('\', \'', $value) . '\')';
                } else if(is_array($value)) {
                    $value = array_map('mysql_escape_string', $value);
                    $condition = $fieldstr . ' '.$tmp.' (\'' . implode('\', \'', $value) . '\')';
                } else {
                    Logger::error('"in" condition value is not an array or string');
                }
                break;
            case 'like':
            case '!like':
            case 'unlike':
                //unlike一般会使用不到
                $tmp = $symbol == 'like' ? 'LIKE' : 'NOT LIKE';
                if(is_string($value)) {
                    $value = mysql_escape_string($value);
                    //like 选项left,right,默认都有
                    $left = isset($option['left']) ? $option['left'] : true;
                    $right = isset($option['right']) ? $option['right'] : true;
                    $condition = $fieldstr . ' '.$tmp.' \'';
                    $condition .= $left ? '%' : '';
                    $condition .= mysql_escape_string($value);
                    $condition .= $right ? '%' : '';
                    $condition .= '\'';
                } else {
                    Logger::error('"like" condition value is not a string');
                }
                break;
            default:
                $value = mysql_escape_string($value);
                $condition = $fieldstr . ' = \'' . $value . '\'';
                break;
        }
        return $condition;
    }
    public function setOrder($order) {
        if(empty($order)) {
            //Logger::error('empty info conditions array');
        } else if(is_array($order)) {
            $signs = array('DESC', 'ASC');
            foreach($order as $field => $desc) {
                $desc = strtoupper($desc);
                $desc = in_array($desc, $signs) ? $desc : 'DESC';
                $this->order .= $this->order ? ', ' : ' ';
                if($this->model) {
                    $columns = $this->model->columns();
                    if(array_search($field, $columns)) {
                        $this->order .= '`'.$field.'` '.$desc;
                    } else if(array_key_exists($field, $columns)) {
                        $field = $columns[$field];
                        $this->order .= '`'.$field.'` '.$desc;
                    } else {
                        Logger::error('invlid field');
                    }
                } else {
                    $this->order .= '`'.$field.'` '.$desc;
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
            $offset = !isset($limit['offset']) ? isset($limit['0']) ? $limit['0'] : 0 : $limit['offset'];
            $num = !isset($limit['num']) ? isset($limit['1']) ? $limit['1'] : 20 : $limit['num'];
            $this->setOffset($offset);
            $this->setNum($num);
        } else if(is_array($limit)) {
            $num = !isset($limit['num']) ? isset($limit['0']) ? $limit['0'] : 20 : $limit['num'];
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
    /**
     * make select sql
     *
     * @return string
     */
    public function makeSelect() {
        $this->sql = $this->operation = 'SELECT';
        $this->makeFields();
        $this->makeFromTable();
        $this->makeCondition();
        $this->makeGroup();
        $this->makeHaving();
        $this->makeOrder();
        $this->makeLimit();
        return $this->sql;
    }
    /**
     * make insert sql
     *
     * @return string
     */
    public function makeInsert() {
        $this->sql = $this->operation = 'INSERT';
        $this->makeIntoTable();
        $this->makeIntoFields();
        $this->makeValues();
        return $this->sql;
    }
    /**
     * make replace sql
     *
     * @return string
     */
    public function makeReplace() {
        $this->sql = $this->operation = 'REPLACE';
        $this->makeIntoTable();
        $this->makeIntoFields();
        $this->makeValues();
        return $this->sql;
    }
    /**
     * make delete sql
     *
     * @return string
     */
    public function makeDelete() {
        $this->sql = $this->operation = 'DELETE';
        $this->makeFromTable();
        $this->makeStrictCondition();
        return $this->sql;
    }
    /**
     * make update sql
     *
     * @return string
     */
    public function makeUpdate() {
        $this->sql = $this->operation = 'UPDATE';
        $this->makeTable();
        $this->makeSetter();
        $this->makeStrictCondition();
        return $this->sql;
    }
    public function makeCount() {
        $this->sql = $this->operation = 'SELECT';
        $this->makeCountField();
        $this->makeFromTable();
        $this->makeCondition();
        $this->makeGroup();
        $this->makeHaving();
        return $this->sql;
    }
    private function makeCountField() {
        $this->fields = 'COUNT(*)';
        $this->sql .= ' ' . $this->fields;
    }
    private function makeIntoFields() {
        if($this->fields) {
            $this->sql .= ' (' . $this->fields . ')';
        } else {
            Logger::error('empty into fields!');
        }
    }
    private function makeFields() {
        if($this->fields) {
            $this->sql .= ' ' . $this->fields;
        } else if($this->model) {
            $fields = $this->model->toFields();
            $this->sql .= ' ' . '`' . implode('`, `', $fields) . '`';
        } else {
            $this->sql .= ' *';
        }
    }
    private function makeFromTable() {
        if($this->table && $this->schema) {
            $this->sql .= ' FROM ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' FROM ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeFromTable();
        } else {
            Logger::error('no given table name!');
        }
    }
    private function makeIntoTable() {
        if($this->table && $this->schema) {
            $this->sql .= ' INTO ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' INTO ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeIntoTable();
        } else {
            Logger::error('no given table name!');
        }
    }
    private function makeTable() {
        if($this->table && $this->schema) {
            $this->sql .= ' ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeTable();
        } else {
            Logger::error('no given table name!');
        }
    }
    private function makeValues() {
        if($this->values) {
            $this->sql .= ' VALUES (' . $this->values . ')';
        } else {
            Logger::error('values empty!');
        }
    }
    private function makeSetter() {
        if($this->setter) {
            $this->sql .= ' SET ' . $this->setter;
        }
    }
    private function makeCondition() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        }
    }
    private function makeStrictCondition() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        } else {
            $this->sql .= ' WHERE 1 = 0';
        }
    }
    private function makeGroup() {
        if($this->group) {
            $this->sql .= ' GROUP BY ' . $this->group;
        }
    }
    private function makeHaving() {
        if($this->group && $this->having) {
            $this->sql .= ' HAVING ' . $this->having;
        }
    }
    private function makeOrder() {
        if($this->order) {
            $this->sql .= ' ORDER BY ' . $this->order;
        }
    }
    private function makeLimit() {
        if($this->offset > 0) {
            $this->sql .= ' LIMIT ' . $this->offset . ',' . $this->num;
        } elseif($this->num > 0) {
            $this->sql .= ' LIMIT ' . $this->num;
        }
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
            $field = trim($setter[0], ' `');
            $value = trim($setter[1]);
            // 如果两边有单引号则去除掉
            $value = preg_replace('/^\'(.*)\'$/', '$1', $value);
            return array(
                    $field => $value
            );
        } else {
            Logger::error('invalid set string or array to explode!');
            return false;
        }
    }
}
?>

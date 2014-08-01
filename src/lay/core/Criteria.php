<?php
/**
 * SQL处理器
 *
 * @author Lay Li
 */
namespace lay\core;

use lay\util\Util;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * SQL处理器
 *
 * @author Lay Li
 */
class Criteria {
    /**
     * Model
     * @var Model
     */
    protected $model = false;
    /**
     * modifier
     * @var string
     */
    protected $modifier = true;
    /**
     * current operation flag
     * @var string
     */
    protected $operation = 'SELECT';
    /**
     * query fields string
     * @var array
     */
    protected $fields = '';
    /**
     * values info string
     * @var string
     */
    protected $values = '';
    /**
     * sets info string
     * @var string
     */
    protected $setter = '';
    /**
     * schema name string
     * @var string
     */
    protected $schema = '';
    /**
     * table name string
     * @var string
     */
    protected $table = '';
    /**
     * query condition string
     * @var string
     */
    protected $condition = '';
    /**
     * group condition string
     * @var string
     */
    protected $group = '';
    /**
     * having condition string
     * @var string
     */
    protected $having = '';
    /**
     * sorting condition string
     * @var string
     */
    protected $order = '';
    /**
     * limit first number
     * @var int
     */
    protected $offset = - 1; // for paging
    /**
     * limit second number
     * @var int
     */
    protected $num = - 1; // for paging
    /**
     * SQL string
     * @var string
     */
    protected $sql = '';
    
    /**
     * please always set model
     *
     * @param Model $model Model
     */
    public function __construct($model = false) {
        $this->setModel($model);
    }
    /**
     * 设置是否使用着重号
     * @param string $modifier if using modifier
     */
    public function setModifier($modifier = true) {
        $this->modifier = $modifier ? true : false;
    }
    /**
     * 设置model属性
     * @param Model $model Model
     */
    public function setModel($model) {
        if(is_subclass_of($model, 'lay\core\Model')) {
            $this->model = $model;
            $this->setTable($model->table());
            $this->setSchema($model->schema());
        }
    }
    /**
     * 设置SQL FIELDS部分
     *
     * @param array $fields field array
     */
    public function setFields(array $fields, $modelize = true) {
        $m = $this->modifier;
        if(empty($fields)) {
            // Logger::error('empty fields');
        } else if(is_array($fields) && $modelize && $this->model) {
            $tmp = array();
            // 去除可能存在于两边的着重号
            // $fields = $this->trimModifier($fields);
            $columns = $this->model->columns();
            foreach($fields as $field) {
                if($key = array_search($field, $columns)) {
                    // $tmp[] = $field;
                    if($this->modifier) {
                        $tmp[] = '`' . $field . '` AS `' . $key . '`';
                    } else {
                        $tmp[] = $field . ' AS ' . $key;
                    }
                } else if(array_key_exists($field, $columns)) {
                    // $tmp[] = $columns[$field];
                    if($this->modifier) {
                        $tmp[] = '`' . $field . '` AS `' . $key . '`';
                    } else {
                        $tmp[] = $field . ' AS ' . $key;
                    }
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->fields = implode(", ", $tmp);
        } else if(is_array($fields)) {
            // 去除可能存在于两边的着重号
            // $fields = $this->trimModifier($fields);
            if($this->modifier) {
                $fields = $this->untrimModifier($fields);
            }
            $this->fields = implode(", ", $fields);
        } else if(is_string($fields) && $modelize && $this->model) {
            $fields = explode(',', $fields);
            // 去除可能存在于两边的着重号
            $fields = $this->trimModifier($fields);
            $this->setFields($fields);
        } else if(is_string($fields)) {
            $this->fields = $fields;
        } else {
            Logger::error('invalid fields');
        }
    }
    /**
     * 设置INTO中的VALUES部分，同时也将INTO中FIELDS部分设置了
     * 注：传入参数不支持string类型
     *
     * @param array $values value array
     */
    public function setValues(array $values) {
        $m = $this->modifier;
        if(empty($values)) {
            // Logger::error('empty values');
        } else if(is_array($values) && $this->model) {
            $tmpfields = array();
            $tmpvalues = array();
            $columns = $this->model->columns();
            foreach($values as $field => $value) {
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                if(array_search($field, $columns)) {
                    $tmpfields[] = $field;
                    $tmpvalues[] = addslashes($value);
                } else if(array_key_exists($field, $columns)) {
                    $tmpfields[] = $columns[$field];
                    $tmpvalues[] = addslashes($value);
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            if($this->modifier) {
                $tmpfields = $this->untrimModifier($tmpfields);
            }
            $tmpvalues = $this->untrimQuote($tmpvalues);
            
            $this->fields = ! empty($tmpfields) ? implode(", ", $tmpfields) : '';
            $this->values = ! empty($tmpvalues) ? implode(', ', $tmpvalues) : '';
        } else if(is_array($values)) {
            $fields = array_keys($values);
            // 去除可能存在于两边的着重号
            // $tmpfields = $this->trimModifier($fields);
            $tmpvalues = $this->untrimQuote(array_map('addslashes', $values));
            $this->fields = implode(', ', $tmpfields);
            $this->values = implode(', ', $tmpvalues);
        } else {
            Logger::error('invalid values');
        }
    }
    /**
     * 设置SQL SET部分
     *
     * @param array $info set info array
     */
    public function setSetter(array $info) {
        $m = $this->modifier;
        if(empty($info)) {
            // Logger::error('empty set info');
        } else if(is_array($info) && $this->model) {
            $setter = array();
            $columns = $this->model->columns();
            $pk = $this->model->primary();
            foreach($info as $field => $value) {
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                $value = addslashes($value);
                if(array_search($field, $columns)) {
                    if($pk != $field) {
                        $fieldstr = $this->modifier ? $this->untrimModifier($field) : $field;
                        $valuestr = $this->untrimQuote(addslashes($value));
                        $setter[] = "$fieldstr = $valuestr";
                    }
                } else if(array_key_exists($field, $columns)) {
                    if($pk != $columns[$field]) {
                        $fieldstr = $this->modifier ? $this->untrimModifier($columns[$field]) : $columns[$field];
                        $valuestr = $this->untrimQuote(addslashes($value));
                        $setter[] = "$fieldstr = $valuestr";
                    }
                } else {
                    Logger::warn('invalid field:' . $field);
                }
            }
            $this->setter = implode(', ', $setter);
        } else if(is_array($info)) {
            $setter = array();
            foreach($info as $field => $value) {
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                $fieldstr = $this->modifier ? $this->untrimModifier($field) : $field;
                $valuestr = $this->untrimQuote(addslashes($value));
                $setter[] = "$fieldstr = $valuestr";
            }
            $this->setter = implode(', ', $setter);
        } else if(is_string($info)) {
            $info = explode(',', $info);
            $info = $this->explodeSetter($info);
            $this->setSetter($info);
        } else {
            Logger::error('invalid set info string or array!');
        }
    }
    /**
     * 设置SQL TABLE部分
     * @param string $table table name
     */
    public function setTable($table) {
        $m = $this->modifier;
        if(empty($table)) {
            // Logger::error('empty table name');
        } else if(is_string($table)) {
            // 去除可能存在于两边的着重号
            // $table = $this->trimModifier($table);
            $this->table = $this->modifier ? $this->untrimModifier($table) : $table;
        } else {
            Logger::error('invlid table,table name must be string');
        }
    }
    /**
     * 设置SQL SCHEMA部分
     * @param string $schema schema name
     */
    public function setSchema($schema) {
        if(empty($schema)) {
            // Logger::error('empty schema');
        } else if(is_string($schema)) {
            // 去除可能存在于两边的着重号
            // $schema = $this->trimModifier($schema);
            $this->schema = $this->modifier ? $this->untrimModifier($schema) : $schema;
        } else {
            Logger::error('invlid schema,schema name must be string');
        }
    }
    /**
     * 添加一个SQL条件语句单元
     * @param array $condition condition array 
     */
    public function setCondition($condition) {
        if(empty($condition)) {
            // Logger::error('empty condition');
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
    /**
     * 添加多个SQL条件语句单元
     * @param array $conditions conditions array 
     */
    public function addConditions($conditions) {
        if(empty($conditions)) {
            // Logger::error('empty conditions array');
        } else if(is_array($conditions)) {
            foreach($conditions as $condition) {
                $this->setCondition($condition);
            }
        } else {
            Logger::error('invlid condition array');
        }
    }
    /**
     * 添加简单的SQL条件语句
     * @param array $info
     */
    public function addInfoCondition($info) {
        if(empty($info)) {
            // Logger::error('empty info conditions array');
        } else if(is_array($info)) {
            foreach($info as $field => $value) {
                $this->addCondition($field, $value);
            }
        } else {
            Logger::error('invlid info condition array');
        }
    }
    /**
     * 添加多样的SQL条件语句
     * @param array $mix
     */
    public function addMultiCondition($mix) {
        if(empty($mix)) {
            // Logger::error('empty info conditions array');
        } else if(is_array($mix)) {
            foreach($mix as $field => $value) {
                if(is_numeric($field) && is_array($value)) {
                    $this->setCondition($value);
                } else if($field && is_string($field) && is_array($value)) {
                    if($value[0] !== $field) {
                        array_unshift($value, $field);
                    }
                    $this->setCondition($value);
                } else if($field && is_string($field) && is_string($value)) {
                    $this->addCondition($field, $value);
                } else {
                    $this->setCondition($mix);
                    break;
                }
            }
        } else {
            Logger::error('invlid multi condition array');
        }
    }
    /**
     * 添加一个SQL条件语句单元
     * 
     * @param string $field 字段名
     * @param mixed $value 值
     * @param string $symbol 符号
     * @param string $combine 连接符号，AND、OR等
     * @param array $options 可选项
     */
    public function addCondition($field, $value, $symbol = '=', $combine = 'AND', $options = array()) {
        $m = $this->modifier;
        if(empty($field)) {
            // Logger::error('empty condition field,or empty condition value');
        } else if(is_string($field)) {
            $combine = strtoupper($combine);
            $combines = array(
                    'AND',
                    'OR'
            );
            if(! in_array($combine, $combines)) {
                $combine = 'AND';
            }
            $this->condition .= $this->condition ? ' ' . $combine . ' ' : '';
            
            if($this->model) {
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                $table = $this->model->table();
                // 去除可能存在于两边的着重号
                // $table = $this->trimModifier($table);
                $tablestr = $this->modifier ? $this->untrimModifier($table) : $table;
                // option中存在table参数，一般使用不到，可调节优等级
                $fieldstr = ! empty($options['table']) ? $tablestr . '.' : '';
                $columns = $this->model->columns();
                if(array_search($field, $columns)) {
                    $fieldstr .= $this->modifier ? $this->untrimModifier($field) : $field;
                    $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
                } else if(array_key_exists($field, $columns)) {
                    $fieldstr .= $this->modifier ? $this->untrimModifier($columns[$field]) : $columns[$field];
                    $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
                } else {
                    Logger::error('invlid condition field');
                }
            } else {
                $fieldstr = $this->modifier ? $this->untrimModifier($field) : $field;
                $this->condition .= $this->switchSymbolCondition($symbol, $fieldstr, $value, $options);
            }
        } else {
            Logger::error('invlid condition field');
        }
    }
    /**
     * 组合一个条件语句
     * @param string $symbol
     * @param string $fieldstr
     * @param mixed $value
     * @param array $options
     * @return string
     */
    protected function switchSymbolCondition($symbol, $fieldstr, $value, $options = array()) {
        $condition = '';
        $symbol = strtolower($symbol); // 变成小写
        switch($symbol) {
            case '>':
            case '<':
            case '>=':
            case '<=':
            case '<>':
            case '!=':
            case '=':
                if(is_string($value) || is_numeric($value)) {
                    $value = addslashes(strval($value));
                    $value = $this->untrimQuote($value);
                    $condition = $fieldstr . ' ' . $symbol . ' ' . $value . '';
                } else {
                    Logger::error('"in" condition value is not string');
                }
                break;
            case 'in':
            case '!in':
            case 'unin':
                $tmp = $symbol == 'in' ? 'IN' : 'NOT IN';
                if(is_string($value) || is_numeric($value)) {
                    $value = explode(',', strval($value));
                    // 去除可能存在于两边的单引号
                    // $value = $this->trimQuote($value);
                    $value = array_map('addslashes', $value);
                    $value = $this->untrimQuote($value);
                    $condition = $fieldstr . ' ' . $tmp . ' (' . implode(', ', $value) . ')';
                } else if(is_array($value)) {
                    // 去除可能存在于两边的单引号
                    // $value = $this->trimQuote($value);
                    $value = array_map('addslashes', $value);
                    $value = $this->untrimQuote($value);
                    $condition = $fieldstr . ' ' . $tmp . ' (' . implode(', ', $value) . ')';
                } else {
                    Logger::error('"in" condition value is not an array or string');
                }
                break;
            case 'like':
            case '!like':
            case 'unlike':
                // unlike一般会使用不到
                $tmp = $symbol == 'like' ? 'LIKE' : 'NOT LIKE';
                if(is_string($value)) {
                    // like 选项left,right,默认都有
                    $left = isset($option['left']) ? $option['left'] : true;
                    $right = isset($option['right']) ? $option['right'] : true;
                    // 去除可能存在于两边的单引号
                    // $value = $this->trimQuote($value);
                    $valuestr = $left ? '%' : '';
                    $valuestr .= addslashes($value);
                    $valuestr .= $right ? '%' : '';
                    $valuestr = $this->untrimQuote($valuestr);
                    $condition = $fieldstr . ' ' . $tmp . ' ' . $valuestr;
                } else {
                    Logger::error('"like" condition value is not string');
                }
                break;
            default:
                // 去除可能存在于两边的单引号
                // $value = $this->trimQuote($value);
                if(is_string($value) || is_numeric($value)) {
                    $value = addslashes($value);
                    $valuestr = $this->untrimQuote($value);
                    $condition = $fieldstr . ' = ' . $valuestr;
                } else {
                    Logger::error('"in" condition value is not string');
                }
                break;
        }
        return $condition;
    }
    /**
     * 设置SQL ORDER部分
     * 
     * @param array $order sorting array
     */
    public function setOrder($order) {
        $m = $this->modifier;
        if(empty($order)) {
            // Logger::error('empty info conditions array');
        } else if(is_array($order)) {
            $signs = array(
                    'DESC',
                    'ASC'
            );
            foreach($order as $field => $desc) {
                $desc = strtoupper($desc);
                $desc = in_array($desc, $signs) ? $desc : 'DESC';
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                $this->order .= $this->order ? ', ' : '';
                if($this->model) {
                    $columns = $this->model->columns();
                    if(array_search($field, $columns)) {
                        $fieldstr = $this->untrimModifier($field);
                        $this->order .= $fieldstr . ' ' . $desc;
                    } else if(array_key_exists($field, $columns)) {
                        $fieldstr = $this->untrimModifier($columns[$field]);
                        $this->order .= $fieldstr . ' ' . $desc;
                    } else {
                        Logger::error('invlid field');
                    }
                } else {
                    $fieldstr = $this->untrimModifier($field);
                    $this->order .= $fieldstr . ' ' . $desc;
                }
            }
        } else {
            Logger::error('invlid info conditions array');
        }
    }
    /**
     * 设置SQL LIMT部分
     * 
     * @param array $limit limit array
     */
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
    /**
     * 设置offset属性
     * 
     * @param int $offset limit first number
     */
    public function setOffset($offset) {
        $this->offset = intval($offset);
    }
    /**
     * 设置num属性
     * 
     * @param int $num limit second number
     */
    public function setNum($num) {
        $this->num = intval($num);
    }
    /**
     * 设置SQL GROUP部分
     * 
     * @param array $group group condition array
     */
    public function setGroup($group = array()) {
    }
    /**
     * 设置SQL HAVING部分
     * 
     * @param array $having having condition array
     */
    public function setHaving($having = array()) {
    }
    /**
     * make select sql
     *
     * @return string
     */
    public function makeSelectSQL() {
        $this->sql = $this->operation = 'SELECT';
        $this->makeFieldsSQL();
        $this->makeFromTableSQL();
        $this->makeConditionSQL();
        $this->makeGroupSQL();
        $this->makeHavingSQL();
        $this->makeOrderSQL();
        $this->makeLimitSQL();
        return $this->sql;
    }
    /**
     * make insert sql
     *
     * @return string
     */
    public function makeInsertSQL() {
        $this->sql = $this->operation = 'INSERT';
        $this->makeIntoTableSQL();
        $this->makeIntoFieldsSQL();
        $this->makeValuesSQL();
        return $this->sql;
    }
    /**
     * make replace sql
     *
     * @return string
     */
    public function makeReplaceSQL() {
        $this->sql = $this->operation = 'REPLACE';
        $this->makeIntoTableSQL();
        $this->makeIntoFieldsSQL();
        $this->makeValuesSQL();
        return $this->sql;
    }
    /**
     * make delete sql
     *
     * @return string
     */
    public function makeDeleteSQL() {
        $this->sql = $this->operation = 'DELETE';
        $this->makeFromTableSQL();
        $this->makeStrictConditionSQL();
        return $this->sql;
    }
    /**
     * make update sql
     *
     * @return string
     */
    public function makeUpdateSQL() {
        $this->sql = $this->operation = 'UPDATE';
        $this->makeTableSQL();
        $this->makeSetterSQL();
        $this->makeStrictConditionSQL();
        return $this->sql;
    }
    /**
     * make count sql
     *
     * @return string
     */
    public function makeCountSQL() {
        $this->sql = $this->operation = 'SELECT';
        $this->makeCountFieldSQL();
        $this->makeFromTableSQL();
        $this->makeConditionSQL();
        $this->makeGroupSQL();
        $this->makeHavingSQL();
        return $this->sql;
    }
    /**
     * trim modifier string
     *
     * @param string $var string
     * @return string
     */
    public function trimModifier($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'trimModifier'
            ), $var);
        } else if(is_string($var)) {
            return preg_replace('/^`(.*)`$/', '$1', trim($var));
        }
        return $var;
    }
    /**
     * untrim modifier string
     *
     * @param string $var string
     * @return string
     */
    public function untrimModifier($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'untrimModifier'
            ), $var);
        } else if(is_string($var)) {
            return '`' . $var . '`';
        }
        return $var;
    }
    /**
     * trim quote string
     *
     * @param string $var string
     * @return string
     */
    public function trimQuote($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'trimQuote'
            ), $var);
        } else if(is_string($var)) {
            return preg_replace('/^\'(.*)\'$/', '$1', trim($var));
        }
        return $var;
    }
    /**
     * untrim quote string
     *
     * @param string $var string
     * @return string
     */
    public function untrimQuote($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'untrimQuote'
            ), $var);
        } else if(is_string($var)) {
            return '\'' . $var . '\'';
        }
        return $var;
    }
    /**
     * trim quotes string
     *
     * @param string $var string
     * @return string
     */
    public function trimQuotes($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'trimQuotes'
            ), $var);
        } else if(is_string($var)) {
            return preg_replace('/^"(.*)"$/', '$1', trim($var));
        }
        return $var;
    }
    /**
     * untrim quotes string
     *
     * @param string $var string
     * @return string
     */
    public function untrimQuotes($var) {
        if(is_array($var) && ! empty($var)) {
            return array_map(array(
                    $this,
                    'untrimQuotes'
            ), $var);
        } else if(is_string($var)) {
            return '"' . $var . '"';
        }
        return $var;
    }
    /**
     * explode string,then convert to SET SQL string
     * 
     * @param array $str
     * @return array
     */
    protected function explodeSetter($str) {
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
     * make COUNT SQL field clause
     */
    protected function makeCountFieldSQL() {
        $this->fields = 'COUNT(*)';
        $this->sql .= ' ' . $this->fields;
    }
    /**
     * make SQL INSERT INTO fields clause
     */
    protected function makeIntoFieldsSQL() {
        if($this->fields) {
            $this->sql .= ' (' . $this->fields . ')';
        } else {
            Logger::error('empty into fields!');
        }
    }
    /**
     * make fields SQL clause
     */
    protected function makeFieldsSQL() {
        if($this->fields) {
            $this->sql .= ' ' . $this->fields;
        } else if($this->model) {
            $this->setFields($this->model->toFields());
            $this->makeFieldsSQL();
        } else {
            $this->sql .= ' *';
        }
    }
    /**
     * make FROM TABLE SQL clause
     */
    protected function makeFromTableSQL() {
        if($this->table && $this->schema) {
            $this->sql .= ' FROM ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' FROM ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeFromTableSQL();
        } else {
            Logger::error('no given table name!');
        }
    }
    /**
     * make INTO TABLE SQL clause
     */
    protected function makeIntoTableSQL() {
        if($this->table && $this->schema) {
            $this->sql .= ' INTO ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' INTO ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeIntoTableSQL();
        } else {
            Logger::error('no given table name!');
        }
    }
    /**
     * make normal TABLE SQL clause
     */
    protected function makeTableSQL() {
        if($this->table && $this->schema) {
            $this->sql .= ' ' . $this->schema . '.' . $this->table;
        } else if($this->table) {
            $this->sql .= ' ' . $this->table;
        } else if($this->model) {
            $this->setTable($this->model->table());
            $this->setSchema($this->model->schema());
            $this->makeTableSQL();
        } else {
            Logger::error('no given table name!');
        }
    }
    /**
     * make VALUES SQL clause
     */
    protected function makeValuesSQL() {
        if($this->values) {
            $this->sql .= ' VALUES (' . $this->values . ')';
        } else {
            Logger::error('values empty!');
        }
    }
    /**
     * make SET SQL clause
     */
    protected function makeSetterSQL() {
        if($this->setter) {
            $this->sql .= ' SET ' . $this->setter;
        } else {
            Logger::error('setter empty!');
        }
    }
    /**
     * make WHERE SQL clause
     */
    protected function makeConditionSQL() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        }
    }
    /**
     * make strict WHERE SQL clause
     */
    protected function makeStrictConditionSQL() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        } else {
            $this->sql .= ' WHERE 1 = 0';
        }
    }
    /**
     * make GROUP BY SQL clause
     */
    protected function makeGroupSQL() {
        if($this->group) {
            $this->sql .= ' GROUP BY ' . $this->group;
        }
    }
    /**
     * make HAVING SQL clause
     */
    protected function makeHavingSQL() {
        if($this->group && $this->having) {
            $this->sql .= ' HAVING ' . $this->having;
        }
    }
    /**
     * make ORDER BY SQL clause
     */
    protected function makeOrderSQL() {
        if($this->order) {
            $this->sql .= ' ORDER BY ' . $this->order;
        }
    }
    /**
     * make LIMT SQL clause
     */
    protected function makeLimitSQL() {
        if($this->offset > 0) {
            $this->sql .= ' LIMIT ' . $this->offset . ',' . $this->num;
        } elseif($this->num > 0) {
            $this->sql .= ' LIMIT ' . $this->num;
        }
    }
}
?>

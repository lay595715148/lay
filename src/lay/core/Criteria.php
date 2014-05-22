<?php
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
     *
     * @var Model
     */
    private $model = false;
    private $modifier = true;
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
        $this->setModel($model);
    }
    public function setModifier($modifier = true) {
        $this->modifier = $modifier ? true : false;
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
        $m = $this->modifier;
        if(empty($fields)) {
            // Logger::error('empty fields');
        } else if(is_array($fields) && $this->model) {
            $tmp = array();
            // 去除可能存在于两边的着重号
            // $fields = $this->trimModifier($fields);
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
            if($this->modifier) {
                $tmp = $this->untrimModifier($tmp);
            }
            $this->fields = implode(", ", $tmp);
        } else if(is_array($fields)) {
            // 去除可能存在于两边的着重号
            // $fields = $this->trimModifier($fields);
            if($this->modifier) {
                $fields = $this->untrimModifier($fields);
            }
            $this->fields = implode(", ", $fields);
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
     * 设置INTO中的VALUES部分，同时也将INTO中FIELDS部分设置了
     * 注：传入参数不支持string类型
     *
     * @param array $values            
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
     * @param array $info            
     */
    public function setSetter(array $info) {
        $m = $this->modifier;
        if(empty($info)) {
            // Logger::error('empty set info');
        } else if(is_array($info) && $this->model) {
            $setter = array();
            $columns = $this->model->columns();
            foreach($info as $field => $value) {
                // 去除可能存在于两边的着重号
                // $field = $this->trimModifier($field);
                $value = addslashes($value);
                if(array_search($field, $columns)) {
                    $fieldstr = $this->modifier ? $this->untrimModifier($field) : $field;
                    $valuestr = $this->untrimQuote(addslashes($value));
                    $setter[] = "$fieldstr = $valuestr";
                } else if(array_key_exists($field, $columns)) {
                    $fieldstr = $this->modifier ? $this->untrimModifier($columns[$field]) : $columns[$field];
                    $valuestr = $this->untrimQuote(addslashes($value));
                    $setter[] = "$fieldstr = $valuestr";
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
    public function addMultiCondition($mix) {
        if(empty($mix)) {
            // Logger::error('empty info conditions array');
        } else if(is_array($mix)) {
            foreach($mix as $field => $value) {
                if(! is_numeric($field)) {
                    $this->addCondition($field, $value);
                } else if(is_array($value)) {
                    $this->setCondition($value);
                } else {
                    $this->setCondition($mix);
                    break;
                }
            }
        } else {
            Logger::error('invlid multi condition array');
        }
    }
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
                $fieldstr = isset($options['table']) && $options['table'] ? $tablestr . '.' : '';
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
                $value = addslashes($value);
                $condition = $fieldstr . ' ' . $symbol . ' \'' . $value . '\'';
                break;
            case 'in':
            case '!in':
            case 'unin':
                $tmp = $symbol == 'in' ? 'IN' : 'NOT IN';
                if(is_string($value)) {
                    $value = explode(',', $value);
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
                    Logger::error('"like" condition value is not a string');
                }
                break;
            default:
                // 去除可能存在于两边的单引号
                // $value = $this->trimQuote($value);
                $value = addslashes($value);
                $valuestr = $this->untrimQuote($value);
                $condition = $fieldstr . ' = ' . $valuestr;
                break;
        }
        return $condition;
    }
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
    public function makeCountSQL() {
        $this->sql = $this->operation = 'SELECT';
        $this->makeCountFieldSQL();
        $this->makeFromTableSQL();
        $this->makeConditionSQL();
        $this->makeGroupSQL();
        $this->makeHavingSQL();
        return $this->sql;
    }
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
    private function makeCountFieldSQL() {
        $this->fields = 'COUNT(*)';
        $this->sql .= ' ' . $this->fields;
    }
    private function makeIntoFieldsSQL() {
        if($this->fields) {
            $this->sql .= ' (' . $this->fields . ')';
        } else {
            Logger::error('empty into fields!');
        }
    }
    private function makeFieldsSQL() {
        if($this->fields) {
            $this->sql .= ' ' . $this->fields;
        } else if($this->model) {
            $this->setFields($this->model->toFields());
            $this->makeFieldsSQL();
        } else {
            $this->sql .= ' *';
        }
    }
    private function makeFromTableSQL() {
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
    private function makeIntoTableSQL() {
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
    private function makeTableSQL() {
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
    private function makeValuesSQL() {
        if($this->values) {
            $this->sql .= ' VALUES (' . $this->values . ')';
        } else {
            Logger::error('values empty!');
        }
    }
    private function makeSetterSQL() {
        if($this->setter) {
            $this->sql .= ' SET ' . $this->setter;
        } else {
            Logger::error('setter empty!');
        }
    }
    private function makeConditionSQL() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        }
    }
    private function makeStrictConditionSQL() {
        if($this->condition) {
            $this->sql .= ' WHERE ' . $this->condition;
        } else {
            $this->sql .= ' WHERE 1 = 0';
        }
    }
    private function makeGroupSQL() {
        if($this->group) {
            $this->sql .= ' GROUP BY ' . $this->group;
        }
    }
    private function makeHavingSQL() {
        if($this->group && $this->having) {
            $this->sql .= ' HAVING ' . $this->having;
        }
    }
    private function makeOrderSQL() {
        if($this->order) {
            $this->sql .= ' ORDER BY ' . $this->order;
        }
    }
    private function makeLimitSQL() {
        if($this->offset > 0) {
            $this->sql .= ' LIMIT ' . $this->offset . ',' . $this->num;
        } elseif($this->num > 0) {
            $this->sql .= ' LIMIT ' . $this->num;
        }
    }
}
?>

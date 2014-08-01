<?php
namespace lay\core;

class Statment extends Criteria {
    /**
     * bound param value array
     * @var array
     */
    protected $paramValues = array();
    public function bindParamValue($value) {
        $this->paramValues[] = $value;
    }
    public function bindParamValues($values) {
        foreach ($values as $value) {
            $this->bindParamValue($value);
        }
    }
    /**
     * return bound param value array
     * @return array
     */
    public function returnParamValues() {
        return $this->paramValues;
    }
    /**
     * 设置INTO中的VALUES部分，同时也将INTO中FIELDS部分设置了
     * 注：传入参数不支持string类型
     *
     * @param array $values value array
     */
    public function setValues(array $values, $modelize = true) {
        $me = &$this;
        $m = $this->modifier;
        if(empty($values)) {
            // Logger::error('empty values');
        } else if(is_array($values) && $modelize && $this->model) {
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
            //$tmpvalues = $this->untrimQuote($tmpvalues);
	        $marks = array();
	        foreach ($value as $key => &$item) {
                $this->bindParamValue($item);
	            $marks[] = '?';
	        }
            $this->fields = ! empty($tmpfields) ? implode(", ", $tmpfields) : '';
            $this->values = ! empty($tmpvalues) ? implode(', ', $marks) : '';
        } else if(is_array($values)) {
            $fields = array_keys($values);
	        $marks = array();
	        foreach ($value as $key => &$item) {
                $this->bindParamValue($item);
	            $marks[] = '?';
	        }
            // 去除可能存在于两边的着重号
            // $tmpfields = $this->trimModifier($fields);
            //$tmpvalues = $this->untrimQuote(array_map('addslashes', $values));
            $this->fields = implode(', ', $tmpfields);
            $this->values = implode(', ', $marks);
        } else {
            Logger::error('invalid values');
        }
    }
    /**
     * 设置SQL SET部分
     *
     * @param array $info set info array
     */
    public function setSetter(array $info, $modelize = true) {
        $me = &$this;
        $m = $this->modifier;
        if(empty($info)) {
            // Logger::error('empty set info');
        } else if(is_array($info) && $modelize && $this->model) {
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
                        //$valuestr = $this->untrimQuote(addslashes($value));
                        $this->bindParamValue($value);
                        $setter[] = "$fieldstr = ?";
                    }
                } else if(array_key_exists($field, $columns)) {
                    if($pk != $columns[$field]) {
                        $fieldstr = $this->modifier ? $this->untrimModifier($columns[$field]) : $columns[$field];
                        //$valuestr = $this->untrimQuote(addslashes($value));
                        $this->bindParamValue($value);
                        $setter[] = "$fieldstr = ?";
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
                //$valuestr = $this->untrimQuote(addslashes($value));
                $this->bindParamValue($value);
                $setter[] = "$fieldstr = ?";
            }
            $this->setter = implode(', ', $setter);
        } else if(is_string($info)) {
            $info = explode(',', $info);
            $info = $this->explodeSetter($info);
            $this->setSetter($info, $modelize);
        } else {
            Logger::error('invalid set info string or array!');
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
                    $this->bindParamValue($value);
        	        //$value = addslashes(strval($value));
        	        $condition = $fieldstr . ' ' . $symbol . ' ' . '?' . '';
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
        	        $marks = array();
        	        foreach ($value as $key => &$item) {
                        $this->bindParamValue($item);
        	            $marks[] = '?';
        	        }
        	        // 去除可能存在于两边的单引号
        	        // $value = $this->trimQuote($value);
        	        //$value = array_map('addslashes', $value);
        	        //$value = $this->untrimQuote($value);
        	        $condition = $fieldstr . ' ' . $tmp . ' (' . implode(', ', $marks) . ')';
        	    } else if(is_array($value)) {
        	        $marks = array();
        	        foreach ($value as $key => &$item) {
                        $this->bindParamValue($item);
        	            $marks[] = '?';
        	        }
        	        // 去除可能存在于两边的单引号
        	        // $value = $this->trimQuote($value);
        	        //$value = array_map('addslashes', $value);
        	        //$value = $this->untrimQuote($value);
        	        $condition = $fieldstr . ' ' . $tmp . ' (' . implode(', ', $marks) . ')';
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
        	        //$valuestr .= addslashes($value);
        	        $valuestr .= $right ? '%' : '';
                    $this->bindParamValue($valuestr);
        	        //$valuestr = $this->untrimQuote($valuestr);
        	        $condition = $fieldstr . ' ' . $tmp . ' ' . '?';
        	    } else {
        	        Logger::error('"like" condition value is not string');
        	    }
        	    break;
        	default:
        	    // 去除可能存在于两边的单引号
        	    // $value = $this->trimQuote($value);
        	    if(is_string($value) || is_numeric($value)) {
                    $this->bindParamValue($value);
        	        //$value = addslashes($value);
        	        //$valuestr = $this->untrimQuote($value);
        	        $condition = $fieldstr . ' = ' . '?';
        	    } else {
        	        Logger::error('"in" condition value is not string');
        	    }
        	    break;
        }
        return $condition;
    }
}
?>

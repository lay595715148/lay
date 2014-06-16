<?php
/**
 * 基础表数据模型
 * @abstract
 * @author Lay Li
 */
namespace lay\core;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 基础表数据模型
 * @abstract
 * @author Lay Li
 */
abstract class Model extends Bean {
    /**
     * 返回模型对应数据表名或其他数据库中的集合名称
     * @return string
     */
    public abstract function table();
    /**
     * 返回模型属性名与对应数据表字段的映射关系数组
     * @return array
     */
    public abstract function columns();
    /**
     * 返回模型属性名对应数据表主键字段名
     * @return array
     */
    public abstract function primary();
    /**
     * 返回模型对应数据表所在数据库名
     * @return string
     */
    public function schema() {
        return '';
    }
    /**
     * 返回多个模型之间的关系
     * 例:
     * return array(
     *     'job' => 'ExtOperatingJobs',
     * );
     * 'job'是model的一个属性，'ExtOperatingJobs'是关联的模型名
     * @return array
     */
    public function relations() {
        return array();
    }
    /**
     * 模型对象转换为数据数组
     * @return array
     */
    public function toData() {
        $values = array();
        $columns = $this->columns();
        foreach ($this->properties as $k => $v) {
            $field = $columns[$k];
            $values[$field] = $v;
        }
        return $values;
    }
    /**
     * 返回对应数据库表字段的数组
     * @return array
     */
    public function toFields() {
        return array_values($this->columns());
    }
    /**
     * 通过属性名得到表字段名，如果存在返回字段名，如果参数是字段名直接返回，否则false
     * @param string $pro 属性名
     * @return mixed
     */
    public function toField($pro) {
        $columns = $this->columns();
        if(array_key_exists($pro, $columns)) {
            return $columns[$pro];
        } else if(array_search($pro, $columns)) {
            return $pro;
        }
        return false;
    }
    /**
     * 通过表字段名得到属性名，如果存在返回属性名，如果参数是属性名直接返回，否则false
     * @param string $field 字段名
     * @return mixed 
     */
    public function toProperty($field) {
        $columns = $this->columns();
        if(array_key_exists($field, $columns)) {
            return $field;
        } else {
            return array_search($field, $columns);
        }
    }
    /**
     * 重写数据注入方法，兼容字段名
     * @param array $data 数组数据
     * @return Bean
     */
    public function build($data) {
        $columns = $this->columns();
        if(is_array($data)) {
            foreach($this->properties as $k => $v) {
                $field = $columns[$k];
                if(array_key_exists($k, $data)) {
                    $this->$k = $data[$k];
                } else if(array_key_exists($field, $data)) {
                    $this->$k = $data[$field];
                }
            }
        }
        return $this;
    }
}
?>

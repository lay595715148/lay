<?php
if(! defined('INIT_LAY')) {
    exit();
}

/**
 * <p>基础表数据模型</p>
 *
 * @abstract
 */
abstract class Model extends Bean {
    /**
     * return table name
     * @return string
     */
    public abstract function table();
    /**
     * return mapping between object property and table fields
     * @return array
     */
    public abstract function columns();
    /**
     * return table priamry key
     * @return array
     */
    public abstract function primary();
    /**
     * return schema name
     * @return string
     */
    public function schema() {
        return '';
    }
    /**
     * relation between models
     * example:
     * return array(
     *       'job'    => 'ExtOperatingJobs',
     * );     
     * 'job'是model的一个属性，'ExtOperatingJobs'是关联的MODEL名
     * @return array
     */
    public function relations() {
        return array();
    }
    public function toFields() {
        return array_values($this->columns());
    }
    public function toField($pro) {
        $columns = $this->columns();
        if(array_key_exists($pro, $columns)) {
            return $columns[$pro];
        } else if(array_search($pro, $columns)) {
            return $pro;
        }
        return false;
    }
    public function toProperty($field) {
        $columns = $this->columns();
        if(array_key_exists($field, $columns)) {
            return $field;
        } else {
            return array_search($field, $columns);
        }
    }
}
?>

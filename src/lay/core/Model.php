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
     * return table fields
     * @return array
     */
    public abstract function columns();
    /**
     * return table priamry key
     * @return array
     */
    public abstract function primary();
    /**
     * return relation between tables
     * @return array
     */
    public abstract function relations();
}
?>

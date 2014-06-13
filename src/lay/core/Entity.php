<?php
namespace lay\core;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 基础数据结构体
 *
 * @abstract
 */
abstract class Entity extends Bean {
    /**
     * return its summary properties
     * @return array
     */
    public abstract function summary();
    /**
     * return its summary array
     * @return array
     */
    public abstract function toSummary();
}
?>

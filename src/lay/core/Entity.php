<?php
/**
 * 基础数据结构体
 *
 * @abstract
 * @author Lay Li
 */
namespace lay\core;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 基础数据结构体
 *
 * @abstract
 * @author Lay Li
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

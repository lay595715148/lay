<?php
namespace lay\core;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * <p>基础数据模型</p>
 *
 * @abstract
 */
abstract class Entity extends Bean {
    /**
     * return summary of this object
     * @return array
     */
    public abstract function summary();
    public abstract function toSummary();
}
?>

<?php
/**
 * 核心模板抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 核心模板抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractTemplate {
    /**
     * output as template
     */
    public abstract function out();
    /**
     * output as json string
     */
    public abstract function json();
    /**
     * output as xml string
     */
    public abstract function xml();
    /**
     * output as template
     */
    public abstract function display();
}
?>

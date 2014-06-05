<?php
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
abstract class AbstractTemplate extends AbstractObject {
    public abstract function out();
    public abstract function json();
    public abstract function xml();
    public abstract function display();
}
?>

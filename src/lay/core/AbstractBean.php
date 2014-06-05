<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 核心数据抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractBean extends AbstractObject {
    public abstract function toArray();
    public abstract function toObject();
    public abstract function distinct();
    public abstract function build($data);
}
?>

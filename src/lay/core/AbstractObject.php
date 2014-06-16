<?php
/**
 * 核心抽象基础类
 * 
 * @api 
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 核心抽象基础类
 * 
 * @api 
 * @author Lay Li
 * @abstract
 */
abstract class AbstractObject {
    /**
     * 设置对象属性值的魔术方法
     * @param string $name 属性名
     * @param mixed $value 属性值
     * @return void
     */
    public function __set($name, $value) {
        if(!property_exists($this, $name)) {
            Logger::error('There is no property:'.$name.' in class:'.get_class($this));
        }
    }
    /**
     * 获取对象属性值的魔术方法
     * @param string $name 属性名
     * @return mixed
     */
    public function &__get($name) {
        if(!property_exists($this, $name)) {
            Logger::error('There is no property:'.$name.' in class:'.get_class($this));
        }
    }
}
?>

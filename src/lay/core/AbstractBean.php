<?php
/**
 * 核心数据抽象类文件
 * @author Lay Li
 */
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
abstract class AbstractBean {
    /**
     * 返回对象所有属性名的数组
     * @return array
     */
    public abstract function properties();
    /**
     * 返回对象属性名对属性值的数组
     * @return array
     */
    public abstract function toArray();
    /**
     * 返回对象转换为stdClass后的对象
     * @return stdClass
     */
    public abstract function toStdClass();
    /**
     * 清空对象所有属性值
     * @return Bean
     */
    public abstract function distinct();
    /**
     * 将数组中的数据注入到对象中
     * @param array $data 数组数据
     * @return Bean
     */
    public abstract function build($data);
}
?>

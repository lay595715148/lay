<?php
/**
 * 核心业务逻辑处理抽象类
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
 * 核心业务逻辑处理抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractService {
    /**
     * 获取某条记录
     * 
     * @param int|string $id
     *            ID
     * @return array
     */
    public abstract function get($id);
    /**
     * 增加一条记录
     * 
     * @param array $info
     *            数据数组
     * @return boolean
     */
    public abstract function add(array $info);
    /**
     * 删除某条记录
     *
     * @param int|string $id
     *            ID
     * @return boolean
     */
    public abstract function del($id);
    /**
     * 更新某条记录
     * 
     * @param int|string $id
     *            ID
     * @param array $info
     *            数据数组
     * @return boolean
     */
    public abstract function upd($id, array $info);
    /**
     * 某些条件下的记录数
     * 
     * @param array $info
     *            数据数组
     * @return int
     */
    public abstract function count(array $info);
}
?>

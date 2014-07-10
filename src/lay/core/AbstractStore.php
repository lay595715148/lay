<?php
/**
 * 核心数据访问控制抽象类
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
 * 核心数据访问控制抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractStore extends AbstractSingleton {
    /**
     * 连接数据库
     * @return boolean
     */
    public abstract function connect();
    /**
     * 切换数据库
     *
     * @param string $name
     *            名称
     * @return boolean
     */
    public abstract function change($name = '');
    /**
     * do database querying
     *
     * @param mixed $sql
     *            SQL或其他查询结构
     * @param string $encoding
     *            编码
     * @param boolean $showinfo
     *            是否记录查询信息
     */
    public abstract function query($sql, $encoding = '', $showinfo = false);
    /**
     * 获取某条记录
     * 
     * @param int|string $id
     *            ID
     * @return array
     */
    public abstract function get($id);
    /**
     * 删除某条记录
     * 
     * @param int|string $id
     *            ID
     * @return boolean
     */
    public abstract function del($id);
    /**
     * 增加一条记录
     * 
     * @param array $info
     *            数据数组
     * @return boolean
     */
    public abstract function add(array $info);
    
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
    public abstract function count(array $info = array());
    /**
     * close connection
     */
    public abstract function close();
}
?>

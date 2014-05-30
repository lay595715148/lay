<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractStore extends AbstractObject {
    /**
     * 连接数据库
     */
    public abstract function connect();
    /**
     * 切换数据库
     *
     * @param string $name
     *            名称
     */
    public abstract function change($name = '');
    /**
     * do database querying
     *
     * @param fixed $sql
     *            SQL或其他查询结构
     * @param string $encoding
     *            编码
     * @param boolean $showinfo
     *            是否记录查询信息
     */
    public abstract function query($sql, $encoding = '', $showinfo = false);
    /**
     * select by id
     *
     * @param int|string $id
     *            the ID
     */
    public abstract function get($id);
    /**
     * delete by id
     *
     * @param int|string $id
     *            the ID
     */
    public abstract function del($id);
    /**
     * return id,always replace
     *
     * @param array $info
     *            information array
     */
    public abstract function add(array $info);
    /**
     *
     * @param int|string $id
     *            the ID
     * @param array $info
     *            information array
     */
    public abstract function upd($id, array $info);
    /**
     *
     * @param array $info
     *            information array
     */
    public abstract function count(array $info = array());
    /**
     * close connection
     */
    public abstract function close();
}
?>

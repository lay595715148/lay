<?php
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
abstract class AbstractService extends AbstractObject {
    public abstract function get($id);
    public abstract function add(array $info);
    public abstract function del($id);
    public abstract function upd($id, array $info);
    public abstract function count(array $info);
}
?>

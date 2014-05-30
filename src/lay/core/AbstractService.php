<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractService {
    public abstract function get($id);
    public abstract function add(array $info);
    public abstract function del($id);
    public abstract function upd($id, array $info);
}
?>

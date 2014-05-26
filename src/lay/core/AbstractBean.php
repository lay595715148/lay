<?php
if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractBean {
    public abstract function toArray();
    public abstract function toObject();
    public abstract function distinct();
    public abstract function build($data);
}
?>

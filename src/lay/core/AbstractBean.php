<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractBean extends AbstractObject {
    public abstract function toArray();
    public abstract function toObject();
    public abstract function distinct();
    public abstract function build($data);
}
?>

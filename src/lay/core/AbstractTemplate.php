<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractTemplate extends AbstractObject {
    public abstract function out();
    public abstract function json();
    public abstract function xml();
    public abstract function display();
}
?>

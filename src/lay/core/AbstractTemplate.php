<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractTemplate {
    public abstract function out();
    public abstract function json();
    public abstract function xml();
    public abstract function display();
}
?>

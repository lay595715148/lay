<?php
namespace lay\core;

abstract class AbstractSingleton {
    protected static $_SingletonStack = array();

    public static function getInstance(){
        $classname = get_called_class();
        if (empty(self::$_SingletonStack[$classname])){
            self::$_SingletonStack[$classname] = new $classname();
        }
        return self::$_SingletonStack[$classname];
    }

    protected function __construct() {
    }
}
?>

<?php
namespace lay\core;

use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}

abstract class Strict {
    /**
     * magic setter
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value) {
        if(!property_exists($this, $name)) {
            Logger::error('There is no property:'.$name.' in class:'.get_class($this));
        }
    }
    /**
     * magic getter
     *
     * @param string $name
     * @return void
     */
    public function &__get($name) {
        if(!property_exists($this, $name)) {
            Logger::error('There is no property:'.$name.' in class:'.get_class($this));
        }
    }
}
?>

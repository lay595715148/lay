<?php
if(! defined('INIT_LAY'))
    exit();

interface I_EventEmitter {
    public function trigger($eventid, array $params = array());
    public function register($eventid, $func, $level = 0);
}
?>

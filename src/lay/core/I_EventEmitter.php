<?php
if(! defined('INIT_LAY'))
    exit();

interface I_EventEmitter {
    public function trigger($eventid);
    public function register($eventid, $func, array $params = array(), $level = 0);
}
?>

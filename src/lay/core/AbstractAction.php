<?php
if(!defined('INIT_LAY')) {
    exit();
}

abstract class AbstractAction {
    public abstract function onCreate();
    public abstract function onRequest();
    public abstract function onGet();
    public abstract function onPost();
    public abstract function onStop();
    public abstract function onDestroy();
}
?>

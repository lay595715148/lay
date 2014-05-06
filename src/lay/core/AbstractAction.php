<?php
if(! defined('INIT_LAY')) {
    define('INIT_LAY', true);
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

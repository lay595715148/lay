<?php
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 核心动作抽象类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractAction extends AbstractObject {
    public abstract function onCreate();
    public abstract function onRequest();
    public abstract function onGet();
    public abstract function onPost();
    public abstract function onStop();
    public abstract function onDestroy();
}
?>

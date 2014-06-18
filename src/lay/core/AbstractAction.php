<?php
/**
 * 核心动作抽象类
 *
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 核心动作抽象类
 *
 * @author Lay Li
 * @abstract
 */
abstract class AbstractAction {
    /**
     * 创建事件触发方法
     */
    public abstract function onCreate();
    /**
     * REQUEST事件触发方法
     */
    public abstract function onRequest();
    /**
     * GET事件触发方法
     */
    public abstract function onGet();
    /**
     * POST事件触发方法
     */
    public abstract function onPost();
    /**
     * 结束事件触发方法
     */
    public abstract function onStop();
    /**
     * 摧毁事件触发方法
     */
    public abstract function onDestroy();
}
?>

<?php
/**
 * 核心插件抽象类，增加新插件，需继承此类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 核心插件抽象类，增加新插件，需继承此类
 *
 * @api
 * @author Lay Li
 * @abstract
 */
abstract class AbstractPlugin {
    /**
     * 插件名称
     * @var string
     */
    protected $name;
    /**
     * PluginManager
     * @var PluginManager
     */
    protected $manager;
    /**
     * 构造方法
     * @param string $name
     * @param PluginManager $manager
     */
    public function __construct($name, $manager) {
        $this->name = $name;
        $this->manager = $manager;
    }
    /**
     * 初始化
     */
    public abstract function initilize();
    /**
     * 增加钩子名称
     * @param string $hookname
     */
    public function addHookName($hookname) {
        $this->manager->addHookName($hookname);
    }
    /**
     * 删除钩子名称
     * @param string $hookname
     */
    public function removeHookName($hookname) {
        $this->manager->removeHookName($hookname);
    }
    /**
     * 给某个钩子增加回调函数或方法
     * @param string $hookname
     * @param callable $callback
     */
    public function addHook($hookname, $callback) {
        $this->manager->register($hookname, $callback);
    }
    /**
     * 删除某钩子的回调函数或方法
     * @param string $hookname
     * @param callable $callback
     */
    public function removeHook($hookname, $callback) {
        $this->manager->unregister($hookname, $callback);
    }
}
?>

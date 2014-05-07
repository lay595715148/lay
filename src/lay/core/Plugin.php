<?php
if(!defined('INIT_LAY')) {
    exit();
}

abstract class Plugin {
    /**
     * 
     * @var string
     */
    protected $name;
    /**
     * 
     * @var PluginManager
     */
    protected $manager;
    public function __construct($name, $manager) {
        $this->name = $name;
        $this->manager = $manager;
    }
    public abstract function initilize();
    public function addHookName($hookname) {
        $this->manager->addHookName($hookname);
    }
    public function removeHookName($hookname) {
        $this->manager->removeHookName($hookname);
    }
    public function addHook($hookname, $callback) {
        $this->manager->register($hookname, $callback);
    }
    public function removeHook() {
        $this->manager->unregister($hookname, $callback);
    }
}
?>

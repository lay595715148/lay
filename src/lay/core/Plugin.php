<?php
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
    public function addHook($hookname, $callback) {
        $this->manager->register($hookname, $callback);
    }
    public function removeHook() {
        $this->manager->unregister($hookname, $callback);
    }
}
?>

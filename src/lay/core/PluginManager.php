<?php

namespace lay\core;

use lay\App;
use lay\core\AbstractPlugin;
use lay\util\Logger;

if(! defined('INIT_LAY')) {
    exit();
}
class PluginManager {
    private static $_Instance = null;
    /**
     *
     * @return PluginManager
     */
    public static function getInstance() {
        if(self::$_Instance == null) {
            self::$_Instance = new PluginManager();
        }
        return self::$_Instance;
    }
    /**
     * 初始化插件
     *
     * @param array $plugins
     *            plugin array
     */
    public static function initilize($plugins = array()) {
        if(empty($plugins)) {
            $sep = DIRECTORY_SEPARATOR;
            $dir = App::$_RootPath . $sep . 'src' . $sep . 'plugin';
            $dirs = scandir($dir);
            foreach($dirs as $d) {
                if(is_dir($dir . $sep . $d) && $d != '.' && $d != '..' && empty(App::get('plugins.' . $d))) {
                    App::set('plugins.' . $d, array(
                            'name' => $d
                    ));
                }
            }
            $plugins = App::get('plugins');
        }
        if(is_array($plugins)) {
            // 实例化所有前期基础插件，并注册事件中的插件配置
            self::getInstance()->loadPlugins($plugins);
        }
    }
    public static function exec($hookname, $params = array()) {
        self::getInstance()->trigger($hookname, $params);
    }
    public static function activedHooks() {
        return self::getInstance()->getActivedHooks();
    }
    private function __construct() {
    }
    private $_plugins = array(); // 注册在事件中的插件配置，待事件触发时初始化
    private $plugins = array();
    /**
     * 可用的钩子
     *
     * @var array
     */
    private $hooks = array(
            App::H_INIT,
            App::H_STOP,
            Action::H_CREATE,
            Action::H_STOP,
            Service::H_CREATE,
            Store::H_CREATE
    );
    /**
     * 监听器
     *
     * @var array
     */
    private $listeners = array();
    private $activeHook = false;
    private $activedHooks = array();
    public function addHookName($hookname) {
        if(array_search($hookname, $this->hooks)) {
            Logger::warn("hook $hookname has been declared!", 'PLUGIN');
        } else {
            $this->hooks[] = $hookname;
        }
        return true;
    }
    public function getActivedHooks() {
        return $this->activedHooks;
    }
    public function removeHookName($hookname) {
        if($offset = array_search($hookname, $this->hooks)) {
            array_splice($this->hooks, $offset, 1);
        } else {
            Logger::warn("hook $hookname has been removed!", 'PLUGIN');
        }
        return true;
    }
    /**
     * 注册需要监听的插件方法（钩子）
     *
     * @param string $hook            
     * @param callable $callback            
     */
    public function register($hookname, $callback) {
        if(is_callable($callback) && in_array($hookname, $this->hooks)) {
            // 将插件的引用连同方法push进监听数组中
            $this->listeners[$hookname][] = $callback;
            // 处做些日志记录方面的东西
        } else {
            Logger::error("Invalid callback function for $hookname", 'PLUGIN');
        }
    }
    /**
     * Allow a plugin object to unregister a callback.
     *
     * @param string $hook
     *            Hook name
     * @param mixed $callback
     *            String with global function name or array($obj, 'methodname')
     */
    public function unregister($hookname, $callback) {
        $callbackid = array_search($callback, $this->listeners[$hookname]);
        if($callbackid !== false) {
            unset($this->listeners[$hookname][$callbackid]);
        }
    }
    /**
     * 触发某个钩子
     *
     * @param string $hookname            
     * @param array $params            
     */
    public function trigger($hookname, $params) {
        $this->activeHook = $hookname;
        $this->activedHooks[] = $hookname;
        
        if(! is_array($params) && ! empty($params)) {
            $params = array(
                    $params
            );
        }
        
        // 查看要实现的钩子，是否在监听数组之中
        // 循环调用开始
        foreach(( array )$this->listeners[$hookname] as $callback) {
            // 动态调用插件的方法
            $ret = call_user_func_array($callback, $params);
            if($ret && is_array($ret)) {
                $args = $ret + $args;
            }
            
            if($args['break']) {
                break;
            }
        }
        $this->activeHook = false;
        return $args;
        
        // 处做些日志记录方面的东西
    }
    /**
     * Load and init all enabled plugins
     *
     * @param array $plugins
     *            List of configured plugins to load
     * @param array $requires
     *            List of plugins required by the application
     */
    public function loadPlugins($plugins, $requires = array()) {
        foreach($plugins as $plugin) {
            if(is_array($plugin)) {
                if(isset($plugin['open']) && ! $plugin['open']) {
                    continue;
                } else {
                    if(isset($plugin['action']) && $plugin['action']) {
                        $this->_plugins[Action::E_CREATE][] = $plugin;
                        // 注册action初始化时要加载的插件
                        EventEmitter::on(Action::E_CREATE, array(
                                $this,
                                'loadPluginOnAction'
                        ));
                        continue;
                    }
                    if(isset($plugin['service']) && $plugin['service']) {
                        $this->_plugins[Service::E_CREATE][] = $plugin;
                        // 注册service初始化时要加载的插件
                        EventEmitter::on(Service::E_CREATE, array(
                                $this,
                                'loadPluginOnService'
                        ));
                        continue;
                    }
                    if(isset($plugin['store']) && $plugin['store']) {
                        $this->_plugins[Store::E_CREATE][] = $plugin;
                        // 注册store初始化时要加载的插件
                        EventEmitter::on(Store::E_CREATE, array(
                                $this,
                                'loadPluginOnStore'
                        ));
                        continue;
                    }
                    if(! $this->loadVerify($plugin)) {
                        continue;
                    }
                }
                $this->loadPlugin($plugin['name'], $plugin['classname'], $plugin);
            } else if(is_string($plugin)) {
                $this->loadPlugin($plugin);
            }
        }
        
        // check existance of all required core plugins
        foreach($requires as $require) {
            $loaded = false;
            if(is_array($require)) {
                if(array_key_exists($require['name'], $this->plugins)) {
                    $loaded = true;
                }
                // load required core plugin if no derivate was found
                if(! $loaded) {
                    $loaded = $this->loadPlugin($require['name'], $require['classname'], $require);
                }
            } else if(is_string($require)) {
                if(array_key_exists($require, $this->plugins)) {
                    $loaded = true;
                }
                // load required core plugin if no derivate was found
                if(! $loaded) {
                    $loaded = $this->loadPlugin($require);
                }
            }
            // trigger fatal error if still not loaded
            if(! $loaded) {
                Logger::error("Requried plugin $name was not loaded", 'PLUGIN');
            }
        }
    }
    /**
     * Load the specified plugin
     *
     * @param
     *            string Plugin name
     * @param
     *            boolean Force loading of the plugin even if it doesn't match the filter
     *            
     * @return boolean True on success, false if not loaded or failure
     */
    public function loadPlugin($name, $classname = '', $options = array()) {
        // plugin already loaded
        if(array_key_exists($name, $this->plugins)) {
            return true;
        }
        
        $sep = DIRECTORY_SEPARATOR;
        if($options['dir']) {
            $file = App::$_RootPath . $sep . $options['dir'] . $sep . $name . $sep . $name . '.php';
        } else {
            $file = App::$_RootPath . $sep . 'src/plugin' . $sep . $name . $sep . $name . '.php';
        }
        
        if(file_exists($file)) {
            if(! class_exists($classname, false)) {
                include_once $file;
            }
            if(! class_exists($classname, false)) {
                $classname = ucfirst($name) . 'Plugin';
            }
            // instantiate class if exists
            if(class_exists($classname, false)) {
                $plugin = new $classname($name, $this);
                // check inheritance...
                if(is_subclass_of($plugin, 'lay\core\AbstractPlugin')) {
                    $plugin->initilize();
                    $this->plugins[$name] = $plugin;
                    Logger::info("Avaliable plugin:$classname", 'PLUGIN');
                    return true;
                } else {
                    Logger::warn("Invalid plugin:$classname", 'PLUGIN');
                }
            } else {
            }
        } else {
        }
        
        return false;
    }
    /**
     *
     * @param Action $action            
     */
    public function loadPluginOnAction($action) {
        $_plugins = $this->_plugins[Action::E_CREATE];
        foreach($_plugins as $plugin) {
            // $plugin必是数组
            if(! $this->loadVerify($plugin)) {
                continue;
            }
            // $plugin['action']必存在
            if($plugin['action'] != $action->getName() && ! (App::classExists($plugin['action'], false) && is_a($action, $plugin['action']))) {
                continue;
            }
            $this->loadPlugin($plugin['name'], $plugin['classname'], $plugin);
        }
    }
    /**
     *
     * @param Service $service            
     */
    public function loadPluginOnService($service) {
        $_plugins = $this->_plugins[Service::E_CREATE];
        foreach($_plugins as $plugin) {
            // $plugin必是数组
            if(! $this->loadVerify($plugin)) {
                continue;
            }
            // $plugin['service']必存在
            if(! App::classExists($plugin['service'], false) || ! is_a($service, $plugin['service'])) {
                continue;
            }
            $this->loadPlugin($plugin['name'], $plugin['classname'], $plugin);
        }
    }
    /**
     *
     * @param Store $store            
     */
    public function loadPluginOnStore($store) {
        $_plugins = $this->_plugins[Store::E_CREATE];
        foreach($_plugins as $plugin) {
            // $plugin必是数组
            if(! $this->loadVerify($plugin)) {
                continue;
            }
            // $plugin['store']必存在
            if($plugin['store'] != $store->getName() && ! (App::classExists($plugin['store'], false) && is_a($store, $plugin['store']))) {
                continue;
            }
            $this->loadPlugin($plugin['name'], $plugin['classname'], $plugin);
        }
    }
    private function loadVerify($plugin) {
        $time = time();
        $start = strtotime($plugin['start']);
        $end = strtotime($plugin['end']);
        if(isset($plugin['start']) && $start && $time < $start) {
            return false;
        }
        if(isset($plugin['end']) && $end && $time > $end) {
            return false;
        }
        if(isset($plugin['host']) && ! in_array($_SERVER['HTTP_HOST'], explode('|', $plugin['host']))) {
            return false;
        }
        if(isset($plugin['ip']) && ! in_array($_SERVER['SERVER_ADDR'], explode('|', $plugin['ip']))) {
            return false;
        }
        if(isset($plugin['port']) && ! in_array($_SERVER['SERVER_PORT'], explode('|', $plugin['port']))) {
            return false;
        }
        return true;
    }
}
?>

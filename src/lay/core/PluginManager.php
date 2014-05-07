<?php
if(!defined('INIT_LAY')) {
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
     */
    public static function initilize() {
        $instance = self::getInstance();
        $plugins = Lay::get('plugins');
        if(is_array($plugins)) {
            // 实例化所有插件
            /* foreach($plugins as $plugin) {
                $name = $plugin['name'];
                $classname = $plugin['classname'];
                $requires = $plugin['requires'];
                $instance->loadPlugin($plugin, $classname);
            } */
            $instance->loadPlugins($plugins);
        } else {
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
    private $plugins = array();
    /**
     * 可用的钩子
     *
     * @var array
     */
    private $hooks = array(
            Lay::HOOK_INIT,
            Lay::HOOK_STOP,
            Action::HOOK_CREATE,
            Action::HOOK_STOP,
            Action::HOOK_DESTROY
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
            Logger::error("Invalid callback function for $hookname");
        }
    }
    /**
     * Allow a plugin object to unregister a callback.
     *
     * @param string $hook Hook name
     * @param mixed  $callback String with global function name or array($obj, 'methodname')
     */
    public function unregister($hookname, $callback) {
        $callbackid = array_search($callback, $this->listeners[$hookname]);
        if ($callbackid !== false) {
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
        if (!is_array($params) && !empty($params)) {
            $params = array($params);
        }
        
        $this->activeHook = $hookname;
        $this->activedHooks[] = $hookname;
        // 查看要实现的钩子，是否在监听数组之中
        // 循环调用开始
        foreach((array)$this->listeners[$hookname] as $callback) {
            // 动态调用插件的方法
            $ret = call_user_func_array($callback, $params);
            if ($ret && is_array($ret)) {
                $args = $ret + $args;
            }
            
            if ($args['break']) {
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
                $this->loadPlugin($plugin['name'], $plugin['classname'], $plugin['requires']);
            } else if(is_string($plugin)) {
                $this->loadPlugin($plugin);
            }
        }
        
        // check existance of all required core plugins
        foreach($requires as $require) {
            $loaded = false;
            if(is_array($require)) {
                if(array_key_exists($plugin['name'], $this->plugins)) {
                    $loaded = true;
                }
                // load required core plugin if no derivate was found
                if(! $loaded) {
                    $loaded = $this->loadPlugin($plugin['name'], $plugin['classname']);
                }
            } else if(is_string($plugin)) {
                if(array_key_exists($plugin, $this->plugins)) {
                    $loaded = true;
                }
                // load required core plugin if no derivate was found
                if(! $loaded) {
                    $loaded = $this->loadPlugin($name);
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
    public function loadPlugin($name, $classname = '', $force = false) {
        // plugin already loaded
        if(array_key_exists($name, $this->plugins)) {
            return true;
        }
        
        $separator = DIRECTORY_SEPARATOR;
        $file = Lay::$_RootPath . $separator . 'plu' . $separator . $name . $separator . 'index.php';
        
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
                if(is_subclass_of($plugin, 'Plugin')) {
                    $plugin->initilize();
                    $this->plugins[$name] = $plugin;
                    Logger::info("using plugin:$classname", 'PLUGIN');
                    return true;
                }
            } else {
            }
        } else {
        }
        
        return false;
    }
    // public
}
?>

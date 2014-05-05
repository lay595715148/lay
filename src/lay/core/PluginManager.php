<?php
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
            foreach($plugins as $plugin) {
                $name = $plugin['name'];
                $requires = $plugin['requires'];
                $instance->loadPlugin($name, $requires);
            }
        } else {
        }
    }
    public static function exec($hookname, $params = array()) {
        self::getInstance()->trigger($hookname, $params);
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
            'lay_initilize',
            'action_create',
            'action_destroy'
    );
    /**
     * 监听器
     *
     * @var array
     */
    private $listeners = array();
    private $activeHook = false;
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
        // 查看要实现的钩子，是否在监听数组之中
        // 循环调用开始
        foreach((array)$this->listeners[$hookname] as $callback) {
            // 动态调用插件的方法
            $ret = call_user_func($callback, $params);
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
        foreach($plugins as $name) {
            $this->loadPlugin($name);
        }
        
        // check existance of all required core plugins
        foreach($requires as $name) {
            $loaded = false;
            if(array_key_exists($name, $this->plugins)) {
                $loaded = true;
            }
            
            // load required core plugin if no derivate was found
            if(! $loaded) {
                $loaded = $this->loadPlugin($name);
            }
            
            // trigger fatal error if still not loaded
            if(! $loaded) {
                Logger::error("Requried plugin $name was not loaded");
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
    public function loadPlugin($name, $force = false) {
        // plugin already loaded
        if(array_key_exists($name, $this->plugins)) {
            return true;
        }
        
        $separator = DIRECTORY_SEPARATOR;
        $file = Lay::$_RootPath . $separator . 'plu' . $separator . $name . $separator . 'index.php';
        
        if(file_exists($file)) {
            $classname = ucfirst($name) . 'Plugin';
            if(! class_exists($classname, false)) {
                include_once $file;
            }
            // instantiate class if exists
            if(class_exists($classname, false)) {
                $plugin = new $classname($name, $this);
                // check inheritance...
                if(is_subclass_of($plugin, 'Plugin')) {
                    $plugin->initilize();
                    $this->plugins[$name] = $plugin;
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

<?php

namespace lay;

use lay\util\Logger;
use lay\util\Util;
use lay\core\EventEmitter;
use lay\core\Configuration;
use lay\core\PluginManager;
use lay\core\Action;
use Exception;

if(! defined('INIT_LAY')) {
    define('INIT_LAY', true); // 标记
}
error_reporting(E_ALL & ~ E_NOTICE);

/**
 * 主类，创建生命周期
 *
 * @author Lay Li 2014-04-29
 */
final class App {
    const E_CREATE = 'lay_create';
    const E_INIT = 'lay_init';
    const E_STOP = 'lay_stop';
    const E_DESTROY = 'lay_destroy';
    const H_INIT = 'hook_lay_init';
    const H_STOP = 'hook_lay_stop';
    private static $_Instance = null;
    public static $_RootPath = '';
    public static function getInstance() {
        if(self::$_Instance == null) {
            self::$_Instance = new App();
        }
        return self::$_Instance;
    }
    public static function start() {
        global $_START;
        $_START = date('Y-m-d H:i:s') . substr(( string )microtime(), 1, 8);
        self::getInstance()->initilize()->run();
    }
    /**
     * set configuration
     *
     * @param string|array<string> $keystr
     *            the configuring key string explode by '.'
     * @param string|boolean|int|array $value
     *            the configuring value
     * @return void
     */
    public static function set($keystr, $value) {
        Configuration::set($keystr, $value);
    }
    /**
     * get configuration by key string
     *
     * @param string $keystr
     *            the configuring key string explode by '.', example: 'action.index'
     * @param mixed $default
     *            if get nothing by $keystr,the default value will return
     * @return mixed
     */
    public static function get($keystr = '', $default = null) {
        if(($ret = Configuration::get($keystr)) === null) {
            return $default;
        } else {
            return $ret;
        }
    }
    public static function getActionConfig($name) {
        if(empty($name))
            return Configuration::get('actions');
        else
            return Configuration::get('actions.' . $name);
    }
    public static function getServiceConfig($name) {
        if(empty($name))
            return Configuration::get('services');
        else
            return Configuration::get('services.' . $name);
    }
    public static function getStoreConfig($name) {
        if(empty($name))
            return Configuration::get('stores');
        else
            return Configuration::get('stores.' . $name);
    }
    public static function getTemplateConfig($name) {
        if(empty($name))
            return Configuration::get('templates');
        else
            return Configuration::get('templates.' . $name);
    }
    public static function addClasspath($classpath) {
        self::getInstance()->appendClasspath($classpath);
    }
    public static function addClasspaths($classpaths) {
        self::getInstance()->appendClasspaths($classpaths);
    }
    public static function loadClass($classname, $classpath) {
        self::getInstance()->loadClazz($classname, $classpath);
    }
    public static function classExists($classname, $autoload = true) {
        return class_exists($classname, $autoload) || interface_exists($classname, $autoload);
    }
    // private function
    private $cached = false;
    private $caches = array();
    private $classes = array();
    private $classpath = array(
            'src'
    );
    private $action;
    public function initilize() {
        // $sep = DIRECTORY_SEPARATOR;
        $rootpath = App::$_RootPath;
        // 加载类文件路径缓存
        $this->loadCache();
        // 初始化logger
        Logger::initialize(false);
        // 初始化配置量
        $this->configure($rootpath . "/inc/config/main.env.php");
        // 注册STOP事件 ,最后始终要执行updateCache来更新类文件路径映射， 注意这里增加了级别
        // 用户可以注册在updateCache前的STOP事件
        EventEmitter::on(App::E_STOP, array(
                $this,
                'updateCache'
        ), 1);
        // 设置并加载插件
        PluginManager::initilize();
        
        // 设置其他类自动加载目录路径
        $classpaths = include_once $rootpath . "/inc/config/classpath.php";
        foreach($classpaths as $i => $path) {
            $this->classpath[] = $rootpath . DIRECTORY_SEPARATOR . $path;
        }
        // 触发lay的H_INIT钩子
        PluginManager::exec(App::H_INIT, array(
                $this
        ));
        // 触发INIT事件
        EventEmitter::emit(App::E_INIT, array(
                $this
        ));
        
        return $this;
    }
    /**
     * lay autorun configuration,all config file is load in $_ROOTPATH
     * include actions,services,stores,beans,files...other
     *
     * @param string|array $configuration
     *            a file or file array or config array
     * @param boolean $isFile
     *            sign file,default is true
     * @return void
     */
    public function configure($configuration, $isFile = true) {
        $_ROOTPATH = &App::$_RootPath;
        if(is_array($configuration) && ! $isFile) {
            foreach($configuration as $key => $item) {
                if(is_string($key) && $key) { // key is not null
                    switch($key) {
                        case 'actions':
                        case 'services':
                        case 'stores':
                        case 'beans':
                        case 'models':
                        case 'templates':
                            if(is_array($item)) {
                                $actions = App::get($key);
                                foreach($item as $name => $conf) {
                                    if(is_array($actions) && array_key_exists($name, $actions)) {
                                        Logger::warn('$configuration["' . $key . '"]["' . $name . '"] has been configured', 'CONFIGURE');
                                    } else if(is_string($name) || is_numeric($name)) {
                                        App::set($key . '.' . $name, $conf);
                                    }
                                }
                            } else {
                                Logger::warn('$configuration["' . $key . '"] is not an array', 'CONFIGURE');
                            }
                            break;
                        case 'files':
                            if(is_array($item)) {
                                foreach($item as $file) {
                                    App::configure($file);
                                }
                            } else if(is_string($item)) {
                                $this->configure($item);
                            } else {
                                Logger::warn('$configuration["files"] is not an array or string', 'CONFIGURE');
                            }
                            break;
                        case 'logger':
                            // update Logger
                            Logger::initialize($item);
                        default:
                            App::set($key, $item);
                            break;
                    }
                } else {
                    App::set($key, $item);
                }
            }
        } else if(is_array($configuration)) {
            if(! empty($configuration)) {
                foreach($configuration as $index => $configfile) {
                    $this->configure($configfile);
                }
            }
        } else if(is_string($configuration)) {
            Logger::info('configure file:' . $configuration, 'CONFIGURE');
            if(is_file($configuration)) {
                $tmparr = include_once $configuration;
            } else if(is_file($_ROOTPATH . $configuration)) {
                $tmparr = include_once $_ROOTPATH . $configuration;
            } else {
                Logger::warn($configuration . ' is not a real file', 'CONFIGURE');
                $tmparr = array();
            }
            
            if(empty($tmparr)) {
                $this->configure($tmparr);
            } else {
                $this->configure($tmparr, false);
            }
        } else {
            Logger::warn('unkown configuration type', 'CONFIGURE');
        }
    }
    public function appendClasspath($classpath) {
        $rootpath = App::$_RootPath;
        if(is_dir($rootpath . DIRECTORY_SEPARATOR . $classpath)) {
            $this->classpath[] = $rootpath . DIRECTORY_SEPARATOR . $classpath;
        } else if(is_dir($classpath)) {
            $this->classpath[] = $classpath;
        } else {
            Logger::warn("path:$rootpath" . DIRECTORY_SEPARATOR . "$classpath is not exists!");
        }
        return true;
    }
    public function appendClasspaths($classpaths) {
        $rootpath = App::$_RootPath;
        foreach($classpaths as $path) {
            if(is_dir($rootpath . DIRECTORY_SEPARATOR . $path)) {
                $this->classpath[] = $rootpath . DIRECTORY_SEPARATOR . $path;
            } else {
                Logger::warn("path:$rootpath" . DIRECTORY_SEPARATOR . "$path is not exists!");
            }
        }
        return true;
    }
    private function createActionByConfig($name, $config) {
        if(empty($name) || empty($config)) {
            return false;
        }
        // 两个必有项
        $classname = $config['classname'];
        
        if(! $classname || ! class_exists($classname)) {
            return false;
        }
        if(isset($config['host']) && ! in_array($_SERVER['HTTP_HOST'], explode('|', $config['host']))) {
            return false;
        }
        if(isset($config['ip']) && ! in_array($_SERVER['SERVER_ADDR'], explode('|', $config['ip']))) {
            return false;
        }
        if(isset($config['port']) && ! in_array($_SERVER['SERVER_PORT'], explode('|', $config['port']))) {
            return false;
        }
        
        return Action::getInstance($name, $classname);
    }
    private function createActionByRouter($uri, $router) {
        if(empty($uri) || empty($router)) {
            return false;
        }
        // 两个必有项
        $classname = $router['classname'];
        $name = $router['name'];
        
        if(! $classname || ! class_exists($classname)) {
            return false;
        }
        if(isset($router['host']) && ! in_array($_SERVER['HTTP_HOST'], explode('|', $router['host']))) {
            return false;
        }
        if(isset($router['ip']) && ! in_array($_SERVER['SERVER_ADDR'], explode('|', $router['ip']))) {
            return false;
        }
        if(isset($router['port']) && ! in_array($_SERVER['SERVER_PORT'], explode('|', $router['port']))) {
            return false;
        }
        
        $ismatch = $uri ? preg_match_all($router['rule'], $uri, $matches, PREG_SET_ORDER) : false;
        
        if(! $ismatch) {
            return false;
        } else {
            // 将匹配到的数组放到$_PARAM全局变量中
            global $_PARAM;
            $_PARAM = $matches;
        }
        
        return Action::getInstance($name, $classname);
    }
    private function createAction($name) {
        $routers = App::get('routers');
        // 非给出
        if($name) {
            Logger::info('action name:' . $name);
            $config = App::getActionConfig($name);
            $action = $this->createActionByConfig($name, $config);
        }
        // 非给出name时使用REQUEST_URI
        if(! $action && $_SERVER['REQUEST_URI']) {
            $uri = preg_replace('/^(.*)(\?)(.*)$/', '$1', $_SERVER['REQUEST_URI']);
            Logger::info('action uri:' . $uri);
            $config = App::getActionConfig($uri);
            $action = $this->createActionByConfig($uri, $config);
        }
        // 如果以下没有再正则匹配
        if(! $action && $uri) {
            foreach($routers as $router) {
                $action = $this->createActionByRouter($uri, $router);
                if($action) {
                    break;
                }
            }
        }
        if(! $action) {
            Logger::error('have no action');
        }
        $this->action = $action;
    }
    private function createLifecycle() {
        global $_START, $_END;
        if($this->action) {
            // 注册action的一些事件
            EventEmitter::on(Action::E_GET, array(
                    $this->action,
                    'onGet'
            ), 1);
            EventEmitter::on(Action::E_POST, array(
                    $this->action,
                    'onPost'
            ), 1);
            EventEmitter::on(Action::E_REQUEST, array(
                    $this->action,
                    'onRequest'
            ), 1);
            EventEmitter::on(Action::E_STOP, array(
                    $this->action,
                    'onStop'
            ), 1);
            EventEmitter::on(Action::E_DESTROY, array(
                    $this->action,
                    'onDestroy'
            ), 1);
            
            // 触发action的request事件
            EventEmitter::emit(Action::E_REQUEST, array(
                    $this->action
            ));
            switch($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    // 触发action的get事件
                    EventEmitter::emit(Action::E_GET, array(
                            $this->action
                    ));
                    break;
                case 'POST':
                    // 触发action的post事件
                    EventEmitter::emit(Action::E_POST, array(
                            $this->action
                    ));
                    break;
                default:
                    break;
            }
        }
        
        $_END = date('Y-m-d H:i:s') . substr(( string )microtime(), 1, 8);
        // 触发action的H_STOP钩子
        PluginManager::exec(Action::H_STOP, array(
                $this->action
        ));
        // 触发action的stop事件
        EventEmitter::emit(Action::E_STOP, array(
                $this->action
        ));
        // if is fastcgi
        if(function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        // 触发lay的H_STOP钩子
        PluginManager::exec(App::H_STOP, array(
                $this
        ));
        // 触发STOP事件
        EventEmitter::emit(App::E_STOP, array(
                $this
        ));
        
        $_END = date('Y-m-d H:i:s') . substr(( string )microtime(), 1, 8);
        // Logger::initialize(array(0x01 | 0x02 | 0x10 | 0x20 | 0x21, false));
        Logger::info(json_encode(array(
                $_START,
                $_END
        )));
        // Logger::initialize(false);
    }
    /**
     * 创建Action生命周期
     */
    public function run($name = '') {
        try {
            $this->createAction($name);
        } catch (Exception $e) {
            // catch
        }

        $this->createLifecycle();
        return $this;
    }
    /**
     * 类自动加载
     *
     * @param string $classname
     *            类全名
     * @return void
     */
    public function autoload($classname) {
        if(empty($this->classpath)) {
            $this->checkAutoloadFunctions();
        } else {
            foreach($this->classpath as $path) {
                if(App::classExists($classname, false)) {
                    break;
                } else {
                    $this->loadClazz($classname, $path);
                }
            }
            if(! App::classExists($classname, false)) {
                $this->checkAutoloadFunctions();
            } else {
            }
        }
    }
    /**
     * 判断是否还有其他自动加载函数，如没有则抛出异常
     *
     * @throws Exception
     */
    private function checkAutoloadFunctions() {
        // 判断是否还有其他自动加载函数，如没有则抛出异常
        $funs = spl_autoload_functions();
        $count = count($funs);
        foreach($funs as $i => $fun) {
            if($fun[0] == 'App' && $fun[1] == 'autoload' && $count == $i + 1) {
                Logger::error('Class not found by LAY autoload function');
            }
        }
    }
    /**
     * class load by classpath
     *
     * @param string $classname            
     * @param string $classpath            
     * @return void
     */
    public function loadClazz($classname, $classpath) {
        $classes = $this->classes;
        $suffixes = array(
                '.php',
                '.class.php'
        );
        // 全名映射查找
        if(array_key_exists($classname, $classes)) {
            if(is_file($classes[$classname])) {
                require_once $classes[$classname];
            } else if(is_file($classpath . $classes[$classname])) {
                require_once $classpath . $classes[$classname];
            }
        }
        if(! App::classExists($classname, false)) {
            $tmparr = explode("\\", $classname);
            // 通过命名空间查找
            if(count($tmparr) > 1) {
                $name = array_pop($tmparr);
                $path = $classpath . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmparr);
                $required = false;
                // 命名空间文件夹查找
                if(is_dir($path)) {
                    $tmppath = $path . DIRECTORY_SEPARATOR . $name;
                    foreach($suffixes as $i => $suffix) {
                        if(is_file($tmppath . $suffix)) {
                            $filepath = realpath($tmppath . $suffix);
                            $this->setCache($classname, $filepath);
                            require_once $filepath;
                            break;
                        }
                    }
                }
            }
            if(! App::classExists($classname, false) && preg_match_all('/([A-Z]{1,}[a-z0-9]{0,}|[a-z0-9]{1,})_{0,1}/', $classname, $matches) > 0) {
                // 正则匹配后进行查找
                $tmparr = array_values($matches[1]);
                $prefix = array_shift($tmparr);
                // 如果正则匹配前缀没有找到
                if(! App::classExists($classname, false)) {
                    // 直接以类名作为文件名查找
                    foreach($suffixes as $i => $suffix) {
                        $tmppath = $classpath . DIRECTORY_SEPARATOR . $classname;
                        if(is_file($tmppath . $suffix)) {
                            $filepath = realpath($tmppath . $suffix);
                            $this->setCache($classname, $filepath);
                            require_once $filepath;
                            break;
                        }
                    }
                }
                // 如果以上没有匹配，则使用类名递归文件夹查找，如使用小写请保持（如果第一递归文件夹使用了小写，即之后的文件夹名称保持小写）
                if(! App::classExists($classname, false)) {
                    $path = $lowerpath = $classpath;
                    foreach($matches[1] as $index => $item) {
                        $path .= DIRECTORY_SEPARATOR . $item;
                        $lowerpath .= DIRECTORY_SEPARATOR . strtolower($item);
                        Logger::info('$lowerpath:' . $lowerpath);
                        if(($isdir = is_dir($path)) || is_dir($lowerpath)) { // 顺序文件夹查找
                            $tmppath = ($isdir ? $path : $lowerpath) . DIRECTORY_SEPARATOR . $classname;
                            foreach($suffixes as $i => $suffix) {
                                if(is_file($tmppath . $suffix)) {
                                    $filepath = realpath($tmppath . $suffix);
                                    $this->setCache($classname, $filepath);
                                    require_once $filepath;
                                    break 2;
                                }
                            }
                            continue;
                        } else if($index == count($matches[1]) - 1) {
                            foreach($suffixes as $i => $suffix) {
                                if(($isfile = is_file($path . $suffix)) || is_file($lowerpath . $suffix)) {
                                    $filepath = realpath(($isfile ? $path : $lowerpath) . $suffix);
                                    $this->setCache($classname, $filepath);
                                    require_once $filepath;
                                    break 2;
                                }
                            }
                            break;
                        } else {
                            // 首个文件夹都已经不存在，直接退出loop
                            break;
                        }
                    }
                }
            }
        }
    }
    /**
     */
    private function loadCache() {
        $cachename = realpath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lay-classes.php');
        if(is_file($cachename)) {
            $this->caches = include $cachename;
        } else {
            $this->caches = array();
        }
        if(is_array($this->caches) && ! empty($this->caches)) {
            $this->classes = array_merge($this->classes, $this->caches);
        }
    }
    /**
     * 更新缓存的类文件映射
     *
     * @return number
     */
    public function updateCache() {
        Logger::info('$this->cached:' . $this->cached);
        if($this->cached) {
            // 先读取，再merge，再存储
            $cachename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lay-classes.php';
            if(is_file($cachename)) {
                $caches = include realpath($cachename);
                $this->caches = array_merge($caches, $this->caches);
            }
            // 写入
            $content = Util::array2PHPContent($this->caches);
            $handle = fopen($cachename, 'w');
            $result = fwrite($handle, $content);
            $return = fflush($handle);
            $return = fclose($handle);
            $this->cached = false;
            return $result;
        } else {
            return false;
        }
    }
    /**
     * 将类文件映射缓存起来
     *
     * @param string $classname            
     * @param string $filepath            
     * @return void
     */
    private function setCache($classname, $filepath) {
        $this->cached = true;
        $this->caches[$classname] = realpath($filepath);
    }
    /**
     * 获取缓存起来的类文件映射
     *
     * @return array string
     */
    public function getCache($classname = '') {
        if(is_string($classname) && $classname && isset($this->caches[$classname])) {
            return $this->caches[$classname];
        } else {
            return $this->caches;
        }
    }
    private function __construct() {
        // 构造时把autoload、rootpath和基本的classpath定义好
        $sep = DIRECTORY_SEPARATOR;
        // 使用自定义的autoload方法
        spl_autoload_register(array(
                $this,
                'autoload'
        ));
        // 设置根目录路径
        App::$_RootPath = $rootpath = dirname(dirname(__DIR__));
        // 设置核心类加载路径
        foreach($this->classpath as $i => $path) {
            $this->classpath[$i] = $rootpath . DIRECTORY_SEPARATOR . $path;
        }
        
        EventEmitter::emit(App::E_CREATE, array(
                $this
        ));
    }
    public function __destruct() {
        EventEmitter::emit(App::E_DESTROY, array(
                $this
        ));
    }
}
?>

<?php
/**
 * 应用程序类，创建生命周期
 *
 * @author Lay Li 2014-04-29
 */
namespace lay;

use lay\util\Logger;
use lay\util\Util;
use lay\core\EventEmitter;
use lay\core\Configuration;
use lay\core\PluginManager;
use lay\core\Action;
use lay\config\C;
use Exception;

if(! defined('INIT_LAY')) {
    define('INIT_LAY', true); // 标记
}
error_reporting(E_ALL & ~ E_NOTICE);
// ob_start();
ini_set('output_buffering', 'on');
ini_set('implicit_flush', 'off');

/**
 * 应用程序类，创建生命周期
 *
 * @author Lay Li 2014-04-29
 */
final class App {
    /**
     * 事件常量，初始化时
     *
     * @var string
     */
    const E_INIT = 'lay_init';
    /**
     * 事件常量，结束时
     *
     * @var string
     */
    const E_STOP = 'lay_stop';
    /**
     * 事件常量，摧毁时
     *
     * @var string
     */
    const E_DESTROY = 'lay_destroy';
    /**
     * 钩子常量，初始化时
     *
     * @var string
     */
    const H_INIT = 'hook_lay_init';
    /**
     * 钩子常量，摧毁时
     *
     * @var string
     */
    const H_STOP = 'hook_lay_stop';
    /**
     * App实例
     *
     * @var App
     */
    private static $_Instance = null;
    /**
     * 框架根目录
     *
     * @var string
     */
    public static $_RootPath = '';
    /**
     * 获取App实例
     *
     * @return App
     */
    public static function getInstance() {
        if(self::$_Instance == null) {
            self::$_Instance = new App();
        }
        return self::$_Instance;
    }
    /**
     * 开始，入口方法
     *
     * @return App
     */
    public static function start() {
        global $_START;
        //起始时间
        $_START = time() + substr(( string )microtime(), 1, 8);
        return self::getInstance()->initilize()->run();
    }
    /**
     * 设置某个配置项
     *
     * @param string|array $keystr
     *            键名
     * @param string|boolean|int|array $value
     *            键值
     * @return void
     */
    public static function set($keystr, $value) {
        Configuration::set($keystr, $value);
    }
    /**
     * 获取某个配置项
     *
     * @param string $keystr
     *            键名，子键名配置项使用.号分割
     * @param mixed $default
     *            不存在时的默认值，默认null
     * @return mixed
     */
    public static function get($keystr = '', $default = null) {
        if(($ret = Configuration::get($keystr)) === null) {
            return $default;
        } else {
            return $ret;
        }
    }
    /**
     * 获取某个行为控制层（action）的配置项
     *
     * @param string $name
     *            键名，行为控制层配置的子键名
     * @return mixed
     */
    public static function getActionConfig($name) {
        if(empty($name))
            return Configuration::get('actions');
        else
            return Configuration::get('actions.' . $name);
    }
    /**
     * 获取某个业务逻辑层（service）的配置项
     *
     * @param string $name
     *            键名，业务逻辑层配置的子键名
     * @return mixed
     */
    public static function getServiceConfig($name) {
        if(empty($name))
            return Configuration::get('services');
        else
            return Configuration::get('services.' . $name);
    }
    /**
     * 获取某个数据访问层（store）的配置项
     *
     * @param string $name
     *            键名，数据访问层配置的子键名
     * @return mixed
     */
    public static function getStoreConfig($name) {
        if(empty($name))
            return Configuration::get('stores');
        else
            return Configuration::get('stores.' . $name);
    }
    /**
     * 增加一个类目录
     *
     * @param string $classpath
     *            类目录
     * @return void
     */
    public static function addClasspath($classpath) {
        self::getInstance()->appendClasspath($classpath);
    }
    /**
     * 增加多个类目录
     *
     * @param array $classpaths
     *            类目录数组
     * @return void
     */
    public static function addClasspaths($classpaths) {
        self::getInstance()->appendClasspaths($classpaths);
    }
    /**
     * 在某个类目录下查找并加载某个类对应的类文件
     *
     * @param string $classname
     *            类名
     * @param string $classpath
     *            类目录
     * @return void
     */
    public static function loadClass($classname, $classpath) {
        self::getInstance()->loadClazz($classname, $classpath);
    }
    /**
     * 某个类或接口是否存在
     *
     * @param string $classname
     *            类名或接口名
     * @param boolean $autoload
     *            是否自动加载
     * @return boolean
     */
    public static function classExists($classname, $autoload = true) {
        return class_exists($classname, $autoload) || interface_exists($classname, $autoload);
    }
    /**
     * 是否有新的类路径，控制是否更新缓存的依据
     *
     * @var boolean
     */
    private $cached = false;
    /**
     * 类路径缓存
     *
     * @var array
     */
    private $caches = array();
    /**
     * 自动加载时优先查找的类与类路径的数组
     *
     * @var array
     */
    private $classes = array();
    /**
     * 自动加载类时使用的类目录数组
     *
     * @var array
     */
    private $classpath = array(
    );
    /**
     * 当前行为控制类对象实例
     *
     * @var Action
     */
    private $action;
    /**
     * 获取action属性
     * @param Action $action
     */
    public function getAction() {
        return $this->action;
    }
    /**
     * 初始化App
     *
     * @return App
     */
    public function initilize() {
        // 先把autoload、rootpath和基本的classpath定义好
        $sep = DIRECTORY_SEPARATOR;
        // 使用自定义的autoload方法
        spl_autoload_register(array(
                $this,
                'autoload'
        ));
        // 设置根目录路径
        App::$_RootPath = $rootpath = dirname(dirname(__DIR__));
        // 设置核心类加载路径
        array_unshift($this->classpath, $rootpath . DIRECTORY_SEPARATOR . 'src');
        // 加载类文件路径缓存
        $this->loadCache();
        // 初始化logger
        Logger::initialize();
        // 初始化配置量
        $this->configure($rootpath . "/inc/config/main.env.php");
        // 注册STOP事件 ,最后始终要执行updateCache来更新类文件路径映射， 注意这里增加了级别
        // 用户可以注册在updateCache前的STOP事件
        EventEmitter::on(App::E_STOP, array(
                $this,
                'updateCache'
        ), EventEmitter::L_MIDDLE);
        // 设置并加载插件
        PluginManager::initilize();
        
        // 设置其他类自动加载目录路径
        $classpaths = include_once $rootpath . "/inc/config/classpath.php";
        foreach($classpaths as $i => $path) {
            if(strpos($path, $rootpath) === 0) {
                $this->classpath[] = $path;
            } else if($realpath = realpath($rootpath . DIRECTORY_SEPARATOR . $path)){
                $this->classpath[] = $realpath;
            }
        }
        // 触发lay的H_INIT钩子
        PluginManager::exec(App::H_INIT, array(
                $this
        ));
        // 触发E_INIT事件
        EventEmitter::emit(App::E_INIT, array(
                $this
        ));
        
        return $this;
    }
    /**
     * 加载并设置配置
     *
     * @param string|array $configuration
     *            配置文件或配置数组
     * @param boolean $isFile
     *            标记是否是配置文件
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
    /**
     * 增加一个类目录
     *
     * @param string $classpath
     *            类目录字符串
     * @return void
     */
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
    /**
     * 增加多个类目录
     *
     * @param array $classpaths
     *            类目录数组
     * @return void
     */
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
    /**
     * 通过名称和配置项创建行为控制对象实例
     *
     * @param string $name
     *            名称
     * @param array $config
     *            配置数组
     * @return Action
     */
    private function createActionByConfig($name, $config) {
        if(empty($name) || empty($config)) {
            return false;
        }
        // 两个必有项
        $classname = $config['classname'];
        
        if(! $classname || ! class_exists($classname)) {
            return false;
        }
        //过滤一些配置条件
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
    /**
     * 通过URI和路由规则配置项创建行为控制对象实例
     *
     * @param string $uri
     *            URI，去除了domain和query的部分，例如：/uri
     * @param array $router
     *            路由规则配置数组
     * @return boolean Ambigous unknown, NULL>
     */
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
        //过滤一些配置条件
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
            $_PARAM = $matches[0];
        }
        
        return Action::getInstance($name, $classname);
    }
    /**
     * 创建行为控制对象实例
     *
     * @param string $name
     *            名称
     * @return Action
     */
    private function createAction($name) {
        $routers = App::get('routers');
        if($this->action) {
            return;
        }
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
        if(! $action && $uri && $routers) {
            foreach($routers as $router) {
                $action = $this->createActionByRouter($uri, $router);
                if($action) {
                    break;
                }
            }
        }
        $this->action = $action;
    }
    /**
     * 创建行为控制对象实例的生命同期
     *
     * @return void
     */
    private function createLifecycle() {
        if($this->action) {
            // 注册action的一些事件
            EventEmitter::on(Action::E_GET, array(
                    $this->action,
                    'onGet'
            ), EventEmitter::L_MIDDLE);
            EventEmitter::on(Action::E_POST, array(
                    $this->action,
                    'onPost'
            ), EventEmitter::L_MIDDLE);
            EventEmitter::on(Action::E_REQUEST, array(
                    $this->action,
                    'onRequest'
            ), EventEmitter::L_MIDDLE);
            EventEmitter::on(Action::E_STOP, array(
                    $this->action,
                    'onStop'
            ), EventEmitter::L_MIDDLE);
            EventEmitter::on(Action::E_DESTROY, array(
                    $this->action,
                    'onDestroy'
            ), EventEmitter::L_MIDDLE);
            
            // 直接先触发action的request事件
            EventEmitter::emit(Action::E_REQUEST, array(
                    $this->action
            ));
        }
        
        // 触发action的H_STOP钩子
        PluginManager::exec(Action::H_STOP, array(
                $this->action
        ));
        // 触发action的stop事件
        EventEmitter::emit(Action::E_STOP, array(
                $this->action
        ));
    }
    /**
     * 运行，创建Action生命周期，触发一系列事件和钩子
     *
     * @param string $name
     *            动作名
     * @return App
     */
    public function run($name = '') {
        global $_START, $_END;
        // 创建
        try {
            $this->createAction($name);
        } catch (Exception $e) {
            // catch
            // 404 不处理
        }
        
        // 创建生命周期
        $this->createLifecycle();
        
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
        
        $_END = Util::microtime();
        Logger::info(array(
                $_START,
                $_END
        ));
        return $this;
    }
    /**
     * 类自动加载
     *
     * @param string $classname
     *            类全名
     * @return void
     * @throws Exception
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
     * @return void
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
     * 在某个类目录下查找并加载某个类对应的类文件
     *
     * @param string $classname
     *            类名
     * @param string $classpath
     *            类目录
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
                        Logger::info('$lowerpath:' . $lowerpath.':$classname:'.$classname);
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
     * 加载类路径缓存
     *
     * @return void
     */
    private function loadCache() {
        $cachename = realpath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename(App::$_RootPath) . '.classes.php');
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
     * 更新类路径缓存
     *
     * @return boolean
     */
    public function updateCache() {
        Logger::info('$this->cached:' . $this->cached);
        if($this->cached) {
            // 先读取，再merge，再存储
            $cachename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename(App::$_RootPath) . '.classes.php';
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
     * 设置新的类路径缓存
     *
     * @param string $classname
     *            类名
     * @param string $filepath
     *            类文件路径
     * @return void
     */
    private function setCache($classname, $filepath) {
        $this->cached = true;
        $this->caches[$classname] = realpath($filepath);
    }
    /**
     * 获取某个类路径缓存或所有
     *
     * @param string $classname
     *            类名
     * @return mixed
     */
    public function getCache($classname = '') {
        if(is_string($classname) && $classname && isset($this->caches[$classname])) {
            return $this->caches[$classname];
        } else {
            return $this->caches;
        }
    }
    /**
     * 构造方法
     */
    private function __construct() {
    }
    /**
     * 析造方法
     */
    public function __destruct() {
        // 触发App的摧毁事件
        EventEmitter::emit(App::E_DESTROY, array(
                $this
        ));
    }
}
?>

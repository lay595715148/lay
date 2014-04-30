<?php
if(! defined('INIT_LAY')) {
    define('INIT_LAY', true);
}

/**
 * 主类，创建生命周期
 *
 * @author Lay Li 2014-04-29
 */
class Lay {
    const EVENT_INIT = 0x0001;
    const EVENT_START = 0x0002;
    const EVENT_STOP = 0x0003;
    private static $Cached = false;
    private static $Caches = array();
    private static $Classes = array(
            'Util' => '/lay/util/Util.php',
            'Store' => '/lay/store/Store.php',
            'Configuration' => '/lay/core/Configuration.php',
            'EventEmitter' => '/lay/core/EventEmitter.php',
            'Logger' => '/lay/util/Logger.php',
            'Plugin' => '/lay/core/Plugin.php',
            
            'I_Configuration' => '/lay/core/I_Configuration.php',
            'I_EventEmitter' => '/lay/core/I_EventEmitter.php',
            'I_Logger' => '/lay/util/I_Logger.php'
    );
    private static $ClassPath = array(
            'src'
    );
    private static $Config = array();
    public static $RootPath = '';
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
        return Configuration::get($keystr);
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
     * laywork autorun configuration,all config file is load in $_ROOTPATH
     * include actions,services,stores,beans,files...other
     *
     * @param string|array $configuration
     *            a file or file array or config array
     * @param boolean $isFile
     *            sign file,default is true
     * @return void
     */
    private static function configure($configuration, $isFile = true) {
        $_ROOTPATH = &self::$RootPath;
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
                                $actions = self::get($key);
                                foreach($item as $name => $conf) {
                                    if(is_array($actions) && array_key_exists($name, $actions)) {
                                        Logger::warn('$configuration["' . $key . '"]["' . $name . '"] has been configured', 'CONFIGURE');
                                    } else if(is_string($name) || is_numeric($name)) {
                                        Logger::info('configure ' . $key . ':' . $name . '', 'CONFIGURE');
                                        self::set($key . '.' . $name, $conf);
                                    }
                                }
                            } else {
                                Logger::warn('$configuration["' . $key . '"] is not an array', 'CONFIGURE');
                            }
                            break;
                        case 'files':
                            if(is_array($item)) {
                                foreach($item as $file) {
                                    self::configure($file);
                                }
                            } else if(is_string($item)) {
                                self::configure($item);
                            } else {
                                Logger::warn('$configuration["files"] is not an array or string', 'CONFIGURE');
                            }
                            break;
                        case 'logger':
                            // update Logger
                            Logger::initialize($item);
                        default:
                            self::set($key, $item);
                            break;
                    }
                } else {
                    self::set($key, $item);
                }
            }
        } else if(is_array($configuration)) {
            if(! empty($configuration)) {
                foreach($configuration as $index => $configfile) {
                    self::configure($configfile);
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
                self::configure($tmparr);
            } else {
                self::configure($tmparr, false);
            }
        } else {
            Logger::warn('unkown configuration type', 'CONFIGURE');
        }
    }
    /**
     * 初始化并触发EVENT_INIT事件
     */
    private static function initilize() {
        // 使用自定义的autoload方法
        spl_autoload_register('Lay::autoload');
        // 设置根目录路径
        self::$RootPath = $rootpath = dirname(dirname(__DIR__));
        // 设置核心类加载路径
        foreach(self::$ClassPath as $i => $path) {
            self::$ClassPath[$i] = $rootpath . '/' . $path;
        }
        Logger::initialize(true);
        Logger::debug();
        // 初始化logger
        Logger::initialize(false);
        // 初始化配置量
        self::configure($rootpath . '/inc/config/main.env.php');
        // 设置并加载插件
        
        // 设置其他类自动加载目录路径
        $classpaths = include_once $rootpath . '/inc/config/classpath.php';
        foreach($classpaths as $i => $path) {
            self::$ClassPath[] = $rootpath . '/' . $path;
        }
        // 加载类文件路径缓存
        self::loadCache();
        // 触发INIT事件
        EventEmitter::emit(self::EVENT_INIT);
    }
    /**
     * 创建Action生命周期
     */
    public static function run() {
    }
    /**
     * 打开，初始化并触发EVENT_START事件
     */
    public static function start() {
        self::initilize();
        // 注册START事件
        EventEmitter::on(self::EVENT_START, 'Lay::run');
        EventEmitter::on(self::EVENT_START, 'Lay::stop', array(), 1);
        // 触发START事件
        EventEmitter::emit(self::EVENT_START);
    }
    public static function stop() {
        // if is fastcgi
        if(function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        // 触发STOP事件
        EventEmitter::emit(self::EVENT_STOP);
        Logger::initialize(true);
        Logger::debug();
        Logger::initialize(false);
    }
    /**
     * 类自动加载
     *
     * @param string $classname
     *            类全名
     * @return void
     */
    public static function autoload($classname) {
        if(empty(self::$ClassPath)) {
            self::checkAutoloadFunctions();
        } else {
            foreach(self::$ClassPath as $path) {
                if(class_exists($classname, false) || interface_exists($classname, false)) {
                    break;
                } else {
                    self::loadClass($classname, $path);
                }
            }
            if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                self::checkAutoloadFunctions();
            }
        }
    }
    /**
     * 判断是否还有其他自动加载函数，如没有则抛出异常
     *
     * @throws Exception
     */
    private static function checkAutoloadFunctions() {
        // 判断是否还有其他自动加载函数，如没有则抛出异常
        $funs = spl_autoload_functions();
        $count = count($funs);
        foreach($funs as $i => $fun) {
            if($fun[0] == 'Lay' && $fun[1] == 'autoload' && $count == $i + 1) {
                throw new Exception('Class not found by LAY autoload function');
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
    public static function loadClass($classname, $classpath) {
        $classes = self::$Classes;
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
        if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
            $tmparr = explode("\\", $classname);
            // 通过命名空间查找
            if(count($tmparr) > 1) {
                $name = array_pop($tmparr);
                $path = $classpath . '/' . implode('/', $tmparr);
                $required = false;
                // 命名空间文件夹查找
                if(is_dir($path)) {
                    $tmppath = $path . '/' . $name;
                    foreach($suffixes as $i => $suffix) {
                        if(is_file($tmppath . $suffix)) {
                            $filepath = realpath($tmppath . $suffix);
                            self::setCache($classname, $filepath);
                            require_once $filepath;
                            break;
                        }
                    }
                }
            }
            if(! class_exists($classname, false) && ! interface_exists($classname, false) && preg_match_all('/([A-Z]{1,}[a-z0-9]{0,}|[a-z0-9]{1,})_{0,1}/', $classname, $matches) > 0) {
                // 正则匹配后进行查找
                $tmparr = array_values($matches[1]);
                $prefix = array_shift($tmparr);
                // 如果正则匹配前缀没有找到
                if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                    // 直接以类名作为文件名查找
                    foreach($suffixes as $i => $suffix) {
                        $tmppath = $classpath . '/' . $classname;
                        if(is_file($tmppath . $suffix)) {
                            $filepath = realpath($tmppath . $suffix);
                            self::setCache($classname, $filepath);
                            require_once $filepath;
                            break;
                        }
                    }
                }
                // 如果以上没有匹配，则使用类名递归文件夹查找，如使用小写请保持（如果第一递归文件夹使用了小写，即之后的文件夹名称保持小写）
                if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                    $path = $lowerpath = $classpath;
                    foreach($matches[1] as $index => $item) {
                        $path .= '/' . $item;
                        $lowerpath .= '/' . strtolower($item);
                        Logger::debug('$lowerpath:' . $lowerpath);
                        if(($isdir = is_dir($path)) || is_dir($lowerpath)) { // 顺序文件夹查找
                            $tmppath = ($isdir ? $path : $lowerpath) . '/' . $classname;
                            foreach($suffixes as $i => $suffix) {
                                if(is_file($tmppath . $suffix)) {
                                    $filepath = realpath($tmppath . $suffix);
                                    self::setCache($classname, $filepath);
                                    require_once $filepath;
                                    break 2;
                                }
                            }
                            continue;
                        } else if($index == count($matches[1]) - 1) {
                            foreach($suffixes as $i => $suffix) {
                                if(($isfile = is_file($path . $suffix)) || is_file($lowerpath . $suffix)) {
                                    $filepath = realpath(($isfile ? $path : $lowerpath) . $suffix);
                                    self::setCache($classname, $filepath);
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
    private static function loadCache() {
        $cachename = sys_get_temp_dir() . '/lay-classes.php';
        if(is_file($cachename)) {
            self::$Caches = include $cachename;
        } else {
            self::$Caches = array();
        }
        if(is_array(self::$Caches) && ! empty(self::$Caches)) {
            self::$Classes = array_merge(self::$Classes, self::$Caches);
        }
    }
    /**
     * 更新缓存的类文件映射
     *
     * @return number
     */
    private static function updateCache() {
        Logger::debug('self::$Cached:' . self::$Cached);
        if(self::$Cached) {
            $content = Util::array2PHPContent(self::$Caches);
            $cachename = sys_get_temp_dir() . 'lay-classes.php';
            highlight_string($content);
            $handle = fopen($cachename, 'w');
            $result = fwrite($handle, $content);
            $return = fflush($handle);
            $return = fclose($handle);
            self::$Cached = false;
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
    private static function setCache($classname, $filepath) {
        self::$Cached = true;
        self::$Caches[$classname] = realpath($filepath);
    }
    /**
     * 获取缓存起来的类文件映射
     *
     * @return array string
     */
    private static function getCache($classname = '') {
        if(is_string($classname) && $classname && isset(self::$Caches[$classname])) {
            return self::$Caches[$classname];
        } else {
            return self::$Caches;
        }
    }
    private function __construct() {
    }
}
?>

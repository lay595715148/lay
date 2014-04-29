<?php
if(defined('INIT_LAY')) {
    define('INIT_LAY', true);
}

/**
 * 主类，创建生命周期
 * 
 * @author Lay Li 2014-04-29
 */
class LAY {
    private static $Caches = array();
    private static $Classes = array();
    private static $ClassPath = array(
            'src/lay'
    );
    
    public static $RootPath = '';
    public static function initilize() {
        // 使用自定义的autoload方法
        spl_autoload_register('LAY::autoload');
        // 设置根目录路径
        self::$RootPath = $rootpath = dirname(dirname(__DIR__));
        // 设置核心类加载路径
        foreach(self::$ClassPath as $i => $path) {
            self::$ClassPath[$i] = $rootpath . '/' . $path;
        }
        // 设置其他类自动加载目录路径
        $classpaths = include_once $rootpath . '/inc/config/classpath.php';
        foreach($classpaths as $i => $path) {
            self::$ClassPath[] = Util::isAbsolutePath($path) ? $path : $rootpath . $path;
        }
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
                    self::getInstance()->loadClass($classname, $path);
                }
            }
            if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                Debugger::warn($classname . ':class not found by Layload', 'CLASS_AUTOLOAD');
                self::checkAutoloadFunctions();
            }
        }
    }
    /**
     * 判断是否还有其他自动加载函数，如没有则抛出异常
     * @throws Exception
     */
    private static function checkAutoloadFunctions() {
        // 判断是否还有其他自动加载函数，如没有则抛出异常
        $funs = spl_autoload_functions();
        $count = count($funs);
        foreach($funs as $i => $fun) {
            if($fun[0] == 'Layload' && $fun[1] == 'autoload' && $count == $i + 1) {
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
    public function loadClass($classname, $classpath) {
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
                            self::setCache($classname, $tmppath . $suffix);
                            require_once $tmppath . $suffix;
                            $required = true;
                            break;
                        }
                    }
                }
            }
            if(! class_exists($classname, false) && ! interface_exists($classname, false) && preg_match_all('/([A-Z]{1,}[a-z0-9]{0,}|[a-z0-9]{1,})_{0,1}/', $classname, $matches) > 0) {
                // 正则匹配后进行查找
                $tmparr = array_values($matches[1]);
                $prefix = array_shift($tmparr);
                // 正则匹配前缀查找 //去除使用prefix功能
                /*
                 * if(array_key_exists($prefix, $prefixes)) { // prefix is not good if(is_file($prefixes[$prefix][$classname])) { Debugger::info($prefixes[$prefix][$classname], 'REQUIRE_ONCE'); $this->setCache($classname, $prefixes[$prefix][$classname]); require_once $prefixes[$prefix][$classname]; } else if(is_file($classpath . $prefixes[$prefix][$classname])) { Debugger::info($classpath . $prefixes[$prefix][$classname], 'REQUIRE_ONCE'); $this->setCache($classname, $classpath . $prefixes[$prefix][$classname]); require_once $classpath . $prefixes[$prefix][$classname]; } else { foreach($suffixes as $i => $suffix) { $tmppath = $prefixes[$prefix]['_dir'] . '/' . $classname; if(is_file($tmppath . $suffix)) { Debugger::info($tmppath . $suffix, 'REQUIRE_ONCE'); $this->setCache($classname, $tmppath . $suffix); require_once $tmppath . $suffix; break; } else if(is_file($classpath . $tmppath . $suffix)) { Debugger::info($classpath . $tmppath . $suffix, 'REQUIRE_ONCE'); $this->setCache($classname, $classpath . $tmppath . $suffix); require_once $classpath . $tmppath . $suffix; break; } } } }
                */
                // 如果正则匹配前缀没有找到
                if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                    // 直接以类名作为文件名查找
                    foreach($suffixes as $i => $suffix) {
                        $tmppath = $classpath . '/' . $classname;
                        if(is_file($tmppath . $suffix)) {
                            self::setCache($classname, $tmppath . $suffix);
                            require_once $tmppath . $suffix;
                            break;
                        }
                    }
                }
                // 如果以上没有匹配，则使用类名递归文件夹查找，如使用小写请保持（如果第一递归文件夹使用了小写，即之后的文件夹名称保持小写）
                if(! class_exists($classname, false) && ! interface_exists($classname, false)) {
                    $path = $lowerpath = $classpath;
                    foreach($matches[1] as $index => $item) {
                        $path .= '/' . $item; // Debugger::debug('$path :'.$path);
                        $lowerpath .= '/' . strtolower($item); // Debugger::debug('$lowerpath:'.$lowerpath);
                        if(($isdir = is_dir($path)) || is_dir($lowerpath)) { // 顺序文件夹查找
                            $tmppath = (($isdir) ? $path : $lowerpath) . '/' . $classname;
                            foreach($suffixes as $i => $suffix) {
                                if(is_file($tmppath . $suffix)) {
                                    self::setCache($classname, $tmppath . $suffix);
                                    require_once $tmppath . $suffix;
                                    break 2;
                                }
                            }
    
                            continue;
                        } else if($index == count($matches[1]) - 1) {
                            foreach($suffixes as $i => $suffix) {
                                if(($isfile = is_file($path . $suffix)) || is_file($lowerpath . $suffix)) {
                                    self::setCache($classname, (($isfile) ? $path : $lowerpath) . $suffix);
                                    require_once (($isfile) ? $path : $lowerpath) . $suffix;
                                    break 2;
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
    /**
     * 
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
        if(self::$Cached) {
            $content = Util::array2PHPContent(self::$Caches);
            $cachename = sys_get_temp_dir() . '/lay-classes.php';
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
        self::$Caches[$classname] = $filepath;
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
    
    public static function start() {
        self::initilize();
    }
    
    private function __construct() {
    }
    public function run() {
        
    }
}
?>

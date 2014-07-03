<?php

/**
 * log工具类
 *
 * @author Lay Li
 */
namespace lay\util;

use \lay\App;
use Exception;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * log工具类
 *
 * @author Lay Li
 */
class Logger {
    /**
     * 定义不打印输出或不记录日志的级别
     *
     * @var int
     */
    const L_NONE = 0;
    /**
     * 定义打印输出或记录日志调试信息的级别
     *
     * @var int
     */
    const L_DEBUG = 1; // 1
    /**
     * 定义打印输出或记录日志信息的级别
     *
     * @var int
     */
    const L_INFO = 2; // 2
    /**
     * 定义打印输出或记录日志警告信息的级别
     *
     * @var int
     */
    const L_WARN = 4; // 4
    /**
     * 定义打印输出或记录日志错误信息的级别
     *
     * @var int
     */
    const L_ERROR = 8; // 8
    /**
     * 记录日志级别
     *
     * @var int
     */
    const L_LOG = 16;
    /**
     * 定义打印输出或记录日志所有级别信息的级别
     *
     * @var int
     */
    const L_ALL = 127; // 127
    /**
     * 打印输出级别
     *
     * @var mixed
     */
    private static $_Out = false;
    /**
     * syslog日志级别
     *
     * @var mixed
     */
    private static $_Log = false;
    /**
     * 延迟打印输出毫秒数
     *
     * @var mixed
     */
    private static $_Sleep = false;
    /**
     * Logger实例
     * 
     * @var Logger
     */
    private static $_Instance = null;
    /**
     * Logger是否打印输出过
     *
     * @var boolean
     */
    private static $_HasOutput = false;
    /**
     * 获取debugger实例
     *
     * @return Logger
     */
    private static function getInstance() {
        if(! self::$_Instance) {
            self::$_Instance = new Logger();
        }
        return self::$_Instance;
    }
    /**
     * 当前级别数值与给出的级别数值是否匹配
     *
     * @param int $set
     *            当前级别数值
     * @param int $lv
     *            给出的级别数值
     * @return boolean
     */
    private static function regular($set, $lv = 1) {
        $ret = $lv & $set;
        return $ret === $lv ? true : false;
    }
    /**
     * log级别，包括打印输出和syslog日志
     *
     * @param mixed $debug
     *            级别，如：true; false; array(true, false); array(Logger::L_NONE, Logger::L_WARN | Logger::L_ERROR)
     * @return void
     */
    public static function initialize($debug = false) {
        if(is_bool($debug)) {
            self::$_Out = self::$_Log = $debug;
        } else if(is_array($debug)) {
            $debug['out'] = isset($debug['out']) ? $debug['out'] : isset($debug[0]) ? $debug[0] : false;
            $debug['log'] = isset($debug['log']) ? $debug['log'] : isset($debug[1]) ? $debug[1] : false;
            $debug['sleep'] = isset($debug['sleep']) ? $debug['sleep'] : isset($debug[2]) ? $debug[2] : false;
            self::$_Out = ($debug['out'] === true) ? true : intval($debug['out']);
            self::$_Log = ($debug['log'] === true) ? true : intval($debug['log']);
            self::$_Sleep = $debug['sleep'] ? intval($debug['sleep']) * 1000 : false;
        } else if(is_int($debug)) {
            self::$_Out = self::$_Log = $debug;
        } else if($debug === '') {
            $debug = App::get('debug');
            if($debug === '' || $debug === null) {
                self::$_Out = self::$_Log = false;
            } else {
                self::initialize($debug);
            }
        } else {
            self::$_Out = self::$_Log = false;
        }
    }
    
    /**
     * 记录调试信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @param string $enforce
     *            是否强制打印输出，默认非
     * @return void
     */
    public static function debug($msg, $tag = '', $enforce = false) {
        if($enforce || self::$_Out === true || (self::$_Out && self::regular(intval(self::$_Out), self::L_DEBUG))) {
            self::$_HasOutput = true;
            self::getInstance()->pre($msg, self::L_DEBUG, $tag);
            ob_flush();
            flush();
            usleep(self::$_Sleep);
        }
        if(self::$_Log === true || (self::$_Log && self::regular(intval(self::$_Log), self::L_DEBUG))) {
            self::getInstance()->syslog(json_encode($msg), self::L_DEBUG, $tag);
        }
    }
    /**
     * 记录信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @return void
     */
    public static function info($msg, $tag = '') {
        if(self::$_Out === true || (self::$_Out && self::regular(intval(self::$_Out), self::L_INFO))) {
            self::$_HasOutput = true;
            self::getInstance()->pre($msg, self::L_INFO, $tag);
            ob_flush();
            flush();
            usleep(self::$_Sleep);
        }
        if(self::$_Log === true || (self::$_Log && self::regular(intval(self::$_Log), self::L_INFO))) {
            self::getInstance()->syslog($msg, self::L_INFO, $tag);
        }
    }
    /**
     * 记录警告信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @return void
     */
    public static function warning($msg, $tag = '') {
        self::warn($msg, $tag);
    }
    /**
     * 记录警告信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @return void
     */
    public static function warn($msg, $tag = '') {
        if(self::$_Out === true || (self::$_Out && self::regular(intval(self::$_Out), self::L_WARN))) {
            self::$_HasOutput = true;
            self::getInstance()->pre($msg, self::L_WARN, $tag);
            ob_flush();
            flush();
            usleep(self::$_Sleep);
        }
        if(self::$_Log === true || (self::$_Log && self::regular(intval(self::$_Log), self::L_WARN))) {
            self::getInstance()->syslog($msg, self::L_WARN, $tag);
        }
    }
    /**
     * 记录错误信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @return void
     * @throws Exception
     */
    public static function error($msg, $tag = '') {
        if(self::$_Out === true || (self::$_Out && self::regular(intval(self::$_Out), self::L_ERROR))) {
            self::$_HasOutput = true;
            self::getInstance()->pre($msg, self::L_ERROR, $tag);
            ob_flush();
            flush();
            usleep(self::$_Sleep);
        }
        if(self::$_Log === true || (self::$_Log && self::regular(intval(self::$_Log), self::L_ERROR))) {
            self::getInstance()->syslog($msg, self::L_ERROR, $tag);
        }
        if(is_string($msg)) {
            throw new Exception($msg);
        } else if(is_a($msg, 'Exception')) {
            throw $msg;
        }
    }
    /**
     * 记录日志信息
     *
     * @param string $msg
     *            字符信息字符串
     * @param string $tag
     *            标签名
     * @return void
     */
    public static function log($msg, $tag = '') {
        if(self::$_Out === true || (self::$_Out && self::regular(intval(self::$_Out), self::L_LOG))) {
            self::$_HasOutput = true;
            self::getInstance()->pre($msg, self::L_LOG, $tag);
            ob_flush();
            flush();
            usleep(self::$_Sleep);
        }
        if(self::$_Log === true || (self::$_Log && self::regular(intval(self::$_Log), self::L_LOG))) {
            self::getInstance()->syslog($msg, self::L_LOG, $tag);
        }
    }
    /**
     * 是否打印输出过
     *
     * @return boolean
     */
    public static function hasOutput() {
        return self::$_Out && self::$_HasOutput ? true : false;
    }
    /**
     * 标记已经打印输出过
     */
    public static function hadOutput() {
        self::$_HasOutput = true;
    }
    
    /**
     * 缩短显示字符
     *
     * @param string $string
     *            字符串
     * @param number $front
     *            之前保留长度
     * @param number $follow
     *            之前保留长度
     * @param string $dot
     *            省略部分替代字符串
     * @return string
     */
    protected function cutString($string, $front = 10, $follow = 0, $dot = '...') {
        $strlen = strlen($string);
        if($strlen < $front + $follow) {
            return $string;
        } else {
            $front = abs(intval($front));
            $follow = abs(intval($follow));
            $pattern = '/^(.{' . $front . '})(.*)(.{' . $follow . '})$/';
            $bool = preg_match($pattern, $string, $matches);
            if($bool) {
                $front = $matches[1];
                $follow = $matches[3];
                return $front . $dot . $follow;
            } else {
                return $string;
            }
        }
    }
    /**
     * 通过级别得到不同CSS样式
     *
     * @param int|string $lv
     *            级别
     * @return string
     */
    protected function parseColor($lv) {
        switch($lv) {
            case Logger::L_DEBUG:
            case 'DEBUG':
                $lv = 'color:#0066FF';
                break;
            case Logger::L_INFO:
            case 'INFO':
                $lv = 'color:#006600';
                break;
            case Logger::L_WARN:
            case 'WARN':
                $lv = 'color:#FF9900';
                break;
            case Logger::L_ERROR:
            case 'ERROR':
                $lv = 'color:#FF0000';
                break;
            case Logger::L_LOG:
            case 'LOG':
                $lv = 'color:#CCCCCC';
                break;
        }
        return $lv;
    }
    /**
     * 级别与特定标签之间转换
     *
     * @param int|string $lv
     *            级别
     * @return mixed
     */
    protected function parseLevel($lv) {
        switch($lv) {
            case Logger::L_DEBUG:
                $lv = 'DEBUG';
                break;
            case Logger::L_INFO:
                $lv = 'INFO';
                break;
            case Logger::L_WARN:
                $lv = 'WARN';
                break;
            case Logger::L_ERROR:
                $lv = 'ERROR';
                break;
            case Logger::L_LOG:
                $lv = 'LOG';
                break;
            case 'DEBUG':
                $lv = Logger::L_DEBUG;
                break;
            case 'INFO':
                $lv = Logger::L_INFO;
                break;
            case 'WARN':
                $lv = Logger::L_WARN;
                break;
            case 'ERROR':
                $lv = Logger::L_ERROR;
                break;
            case 'LOG':
                $lv = Logger::L_LOG;
                break;
        }
        return $lv;
    }
    /**
     * 获取客户端面IP
     *
     * @return string
     */
    protected function ip() {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
    }
    
    /**
     * 使用syslog记录日志信息
     *
     * @param string $msg
     *            日志信息
     * @param int $lv
     *            级别
     * @param string $tag
     *            标签
     * @return void
     */
    public function syslog($msg, $lv = 1, $tag = '') {
        $stack = debug_backtrace();
        $first = array_shift($stack);
        $second = array_shift($stack);
        while($second['class'] == 'lay\util\Logger') { // 判定是不是还在Logger类里
            $first = $second;
            $second = array_shift($stack);
        }
        $file = $first['file'];
        $line = $first['line'];
        $method = $second['function'];
        $type = $second['type'];
        $class = $second['class'];
        
        if(! $method)
            $method = $class;
        if(! $tag || ! is_string($tag))
            $tag = 'MAIN';
        $lv = $this->parseLevel($lv);
        $ip = $this->ip();
        switch($lv) {
            case Logger::L_DEBUG:
            case 'DEBUG':
                $flag = LOG_DEBUG;
                break;
            case Logger::L_INFO:
            case 'INFO':
                $flag = LOG_INFO;
                break;
            case Logger::L_WARN:
            case 'WARN':
                $flag = LOG_WARNING;
                break;
            case Logger::L_ERROR:
            case 'ERROR':
                $flag = LOG_ERR;
                break;
            case Logger::L_LOG:
            case 'LOG':
                $flag = LOG_SYSLOG;
                break;
            default:
                $flag = LOG_INFO;
                break;
        }
        syslog($flag, date('Y-m-d H:i:s') . '.' . floor(microtime() * 1000) . "\t$ip\t[LAY]\t[$lv]\t[$tag]\t[$file($line)]\t$class$type$method()\t$msg");
    }
    /**
     * 打印输出信息
     *
     * @param string $msg
     *            信息
     * @param int $lv
     *            级别
     * @param string $tag
     *            标签
     * @return void
     */
    public function out($msg, $lv = 1, $tag = '') {
        $stack = debug_backtrace();
        $first = array_shift($stack);
        $second = array_shift($stack);
        while($second['class'] == 'lay\util\Logger') { // 判定是不是还在Logger类里
            $first = $second;
            $second = array_shift($stack);
        }
        $file = $first['file'];
        $line = $first['line'];
        $method = $second['function'];
        $type = $second['type'];
        $class = $second['class'];
        
        if(! $method)
            $method = $class;
        if(! $tag || ! is_string($tag))
            $tag = 'MAIN';
        $lv = $this->parseLevel($lv);
        $ip = $this->ip();
        $classexplode = explode("\\", $class);
        echo '<pre style="padding:0px;font-family:Consolas;margin:0px;border:0px;' . $this->parseColor($lv) . '">';
        echo date('Y-m-d H:i:s') . '.' . floor(microtime() * 1000) . "\t$ip\t[$lv]\t[<span title=\"$tag\">" . $this->cutString($tag, 4, 0) . "</span>]\t[<span title=\"$file\">" . $this->cutString($file, 4, 16) . "($line)</span>]\t<span title=\"$class\">" . $class . "</span>$type$method()\t$msg\r\n";
        echo '</pre>';
    }
    /**
     * 打印输出非字符串类型的信息
     *
     * @param string $msg
     *            信息
     * @param int $lv
     *            级别
     * @param string $tag
     *            标签
     * @return void
     */
    public function pre($msg, $lv = 1, $tag = '') {
        $stack = debug_backtrace();
        $first = array_shift($stack);
        $second = array_shift($stack);
        while($second['class'] == 'lay\util\Logger') { // 判定是不是还在Logger类里
            $first = $second;
            $second = array_shift($stack);
        }
        $file = $first['file'];
        $line = $first['line'];
        $method = $second['function'];
        $type = $second['type'];
        $class = $second['class'];
        
        if(! $method)
            $method = $class;
        if(! $tag || ! is_string($tag))
            $tag = 'MAIN';
        $lv = $this->parseLevel($lv);
        $ip = $this->ip();
        $classexplode = explode("\\", $class);
        echo '<pre style="padding:0px;font-family:Consolas;margin:0px;border:0px;' . $this->parseColor($lv) . '">';
        echo date('Y-m-d H:i:s') . '.' . floor(microtime() * 1000) . "\t$ip\t[$lv]\t[<span title=\"$tag\">" . $this->cutString($tag, 4, 0) . "</span>]\t[<span title=\"$file\">" . $this->cutString($file, 4, 16) . "($line)</span>]\t<span title=\"$class\">" . $class . "</span>$type$method()\r\n";
        echo '</pre>';
        echo '<pre style="padding:0 0 0 1em;font-family:Consolas;margin:0px;border:0px;' . $this->parseColor($lv) . '">';
        print_r($msg);
        echo '</pre>';
    }
}
?>
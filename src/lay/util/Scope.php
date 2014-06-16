<?php
/**
 * 变量域工具类
 * 
 * @author Lay Li
 */
namespace lay\util;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 变量域工具类
 * 
 * @author Lay Li
 */
class Scope {
    /**
     * request scope
     * 
     * @var int
     */
    const SCOPE_REQUEST = 0;
    /**
     * get scope
     * 
     * @var int
     */
    const SCOPE_GET = 1;
    /**
     * post scope
     * 
     * @var int
     */
    const SCOPE_POST = 2;
    /**
     * cookie scope
     * 
     * @var int
     */
    const SCOPE_COOKIE = 3;
    /**
     * session scope
     * 
     * @var int
     */
    const SCOPE_SESSION = 4;
    /**
     * regexp ruglar uri parameter scope
     * 
     * @var int
     */
    const SCOPE_PARAM = 5;
    /**
     * header scope
     * 
     * @var int
     */
    const SCOPE_HEADER = 6;
    /**
     * every scope chunks
     * 
     * @var array
     */
    private $chunks = array();
    /**
     * 构造方法
     */
    public function __construct() {
        $this->resolve();
    }
    /**
     * 获取GET变量
     * 
     * @return array
     */
    public function get() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_GET];
    }
    /**
     * 获取POST变量
     * 
     * @return array
     */
    public function post() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_POST];
    }
    /**
     * 获取REQUEST变量
     * 
     * @return array
     */
    public function request() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_REQUEST];
    }
    /**
     * 获取COOKIE变量
     * 
     * @return array
     */
    public function cookie() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_COOKIE];
    }
    /**
     * 获取SESSION变量
     * 
     * @return array
     */
    public function session() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_SESSION];
    }
    /**
     * 获取URL正则匹配后的param变量
     * 
     * @return array
     */
    public function param() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_PARAM];
    }
    /**
     * 获取header变量
     * 
     * @return array
     */
    public function header() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_HEADER];
    }
    /**
     * 计算各个域变量
     * 
     * @param boolean $reset
     *            是否强制重新计算
     * @return array
     */
    public function resolve($reset = false) {
        if(empty($this->chunks) || $reset === true) {
            global $_PARAM;
            $get = $_GET;
            $post = $_POST;
            $request = $_REQUEST;
            $cookie = $_COOKIE;
            $session = $_SESSION;
            $param = is_array($_PARAM) ? $_PARAM : array();
            $header = array();
            // $header = $h
            $this->chunks = array(
                    $get,
                    $post,
                    $request,
                    $cookie,
                    $session,
                    $param,
                    $header
            );
        }
        return $this->chunks;
    }
}
?>

<?php
namespace lay\util;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 变量域工具类
 */
class Scope {
    const SCOPE_REQUEST = 0;
    const SCOPE_GET = 1;
    const SCOPE_POST = 2;
    const SCOPE_COOKIE = 3;
    const SCOPE_SESSION = 4;
    const SCOPE_PARAM = 5;
    const SCOPE_HEADER = 6;
    private $chunks = array();
    public function __construct() {
        $this->resolve();
    }
    /**
     * 获取GET变量
     * @return array
     */
    public function get() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_GET];
    }
    /**
     * 获取POST变量
     * @return array
     */
    public function post() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_POST];
    }
    /**
     * 获取REQUEST变量
     * @return array
     */
    public function request() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_REQUEST];
    }
    /**
     * 获取COOKIE变量
     * @return array
     */
    public function cookie() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_COOKIE];
    }
    /**
     * 获取SESSION变量
     * @return array
     */
    public function session() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_SESSION];
    }
    /**
     * 获取URL正则匹配后的param变量
     * @return array
     */
    public function param() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_PARAM];
    }
    /**
     * 获取header变量
     * @return array
     */
    public function header() {
        $chunk = $this->resolve();
        return $chunk[self::SCOPE_HEADER];
    }
    /**
     * 
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
            $param = is_array($_PARAM)?$_PARAM:array();
            $header = array();
            //$header = $h
            $this->chunks = array($get, $post, $request, $cookie, $session, $param, $header);
        }
        return $this->chunks;
    }
}
?>

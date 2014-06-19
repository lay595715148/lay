<?php
/**
 * 数据创建和收集器
 * @author Lay Li
 */
namespace lay\util;

use lay\entity\Lister;
use lay\entity\Response;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 数据创建和收集器
 * @author Lay Li
 */
class Collector {
    /**
     * 创建Lister
     * @param array $list
     * @param string $total
     * @param int $offset
     * @param int $num
     * @return lay\entity\Lister
     */
    public static function lister($list, $total = false, $offset = -1, $num = -1) {
        $lister = new Lister();
        $lister->list = $list;
        $lister->total = is_numeric($total) ? $total : count($lister->list);
        $lister->hasNext = is_bool($offset) ? $offset : Util::hasNext($lister->total, $offset, $num);
        return $lister;
    }
    /**
     * 创建Response
     * @param string $action
     * @param mixed $content
     * @param boolean $success
     * @param number $code
     * @return lay\entity\Response
     */
    public static function response($action, $content, $success = true, $code = 0) {
        global $_START;
        $response = new Response();
        $response->success = $success;
        $response->action = $action;
        $response->content = $content;
        $response->code = $code;
        $response->exp = Util::microtime() - $_START;
        return $response;
    }
    /**
     * 创建Response，表示失败的情况
     * @param unknown $action
     * @param unknown $msg
     * @param unknown $code
     * @return lay\entity\Response
     */
    public static function errorResponse($action, $msg, $code) {
        return self::response($action, $msg, false, $code);
    }
}
?>

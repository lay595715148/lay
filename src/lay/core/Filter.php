<?php
/**
 * 过滤器接口，增加新过滤器，需实现此接口
 *
 * @api
 * @author Lay Li
 * @abstract
 */
namespace lay\core;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 过滤器接口，增加新过滤器，需实现此接口
 *
 * @api
 * @author Lay Li
 * @abstract
 */
interface Filter {
    /**
     * 初始化
     * @param array $filterConfig 配置项
     */
    public function initFilter($filterConfig);
    /**
     * 执行过滤
     * @param Action $action 实现Action的对象
     * @param Filter $chain 实现Filter的下一个对象
     */
    public function doFilter($action, $chain);
}
?>

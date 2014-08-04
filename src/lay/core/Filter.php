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
     * @param array $config 配置项
     */
    public function initilize($config);
    /**
     * 执行过滤
     * @param Action $action 实现Action的对象
     * @param FilterChain $chain FilterChain
     */
    public function doFilter($action, $chain);
}
?>

<?php
if(! defined('INIT_LAY'))
    exit();

/**
 * 节点数据接口类
 *
 * @author Lay Li
 *
 */
interface I_Configuration {
    /**
     * 设置节点的值
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function setter($key, $value);
    /**
     * 获取节点的值
     *
     * @param mixed $key
     * @return mixed
    */
    public function getter($key);
}
?>

<?php
/**
 * 可设置失效时间的接口
 * @author Lay Li
 */
namespace lay\model;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 可设置失效时间的接口
 * @author Lay Li
 */
interface Expireable {
    /**
     * return lifetime
     * @return int
     */
    public function getLifetime();
    /**
     * set lifetime,it could be timestamp or expire seconds
     * @param int $lifetime 生命期
     */
    public function setLifetime($lifetime);
}
?>

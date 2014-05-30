<?php
namespace lay\core;

if(! defined('INIT_LAY')) {
    define('INIT_LAY', true);
}

/**
 * 可设置失效时间的接口
 * @author Lay Li
 */
interface I_Expireable {
    /**
     * return lifetime
     */
    public function getLifetime();
    /**
     * set lifetime,it could be timestamp or expire seconds
     */
    public function setLifetime($lifetime);
}
?>

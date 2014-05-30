<?php
namespace lay\core;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 自增长接口
 * @author Lay Li
 */
interface I_Increment {
    /**
     * return auto increment primary key
     */
    public function sequence();
}
?>

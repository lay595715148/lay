<?php
if(! defined('INIT_LAY')) {
    define('INIT_LAY', true);
}

/**
 * 生成器接口
 * @see https://github.com/lay595715148/lay
 * 
 * @author Lay Li
 */
interface I_Provider {
    /**
     * provide object instance
     * @param string|array $name name string or config array
     */
    public function provide($name = '');
}
?>

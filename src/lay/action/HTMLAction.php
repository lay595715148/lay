<?php
if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出JSON格式
 * @abstract
 */
abstract class HTMLAction extends Action {
    public function onStop() {
        $this->template->display();
    }
}
?>

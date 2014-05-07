<?php
if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出JSON格式
 * @abstract
 */
abstract class XmlAction extends Action {
    public function onStop() {
        $this->template->xml();
    }
}
?>

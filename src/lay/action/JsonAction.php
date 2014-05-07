<?php
if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出JSON格式
 * @abstract
 */
abstract class JsonAction extends Action {
    public function onStop() {
        parent::onStop();
        $this->template->json();
    }
}
?>

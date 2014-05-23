<?php
if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出JSON格式
 * @abstract
 */
abstract class JSONAction extends Action {
    public function onStop() {
        parent::onStop();
        $this->template->header('Content-Type: application/json');
        $this->template->json();
    }
}
?>

<?php
namespace lay\action;

use \lay\core\Action;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出JSON格式
 * @abstract
 */
abstract class HTMLAction extends Action {
    /**
     * (non-PHPdoc)
     * @see \lay\core\Action::onStop()
     */
    public function onStop() {
        $this->template->display();
    }
}
?>

<?php
/**
 * 输出HTML
 * @author Lay Li
 */
namespace lay\action;

use \lay\core\Action;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出HTML
 * @abstract
 */
abstract class HTMLAction extends Action {
    /**
     * (non-PHPdoc)
     * @see \lay\core\Action::onStop()
     */
    public function onStop() {
        $this->template->display();
        parent::onStop();
    }
}
?>

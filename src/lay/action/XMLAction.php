<?php
/**
 * 输出XML格式
 * @author Lay Li
 */
namespace lay\action;

use \lay\core\Action;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出XML格式
 * @abstract
 */
abstract class XMLAction extends Action {
    /**
     * (non-PHPdoc)
     * @see \lay\core\Action::onStop()
     */
    public function onStop() {
        $this->template->xml();
        parent::onStop();
    }
}
?>

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
abstract class XMLAction extends Action {
    public function onStop() {
        $this->template->header('Content-Type: text/xml');
        $this->template->xml();
    }
}
?>

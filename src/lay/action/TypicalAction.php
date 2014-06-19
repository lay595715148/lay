<?php
/**
 * 输出JSON格式
 * @abstract
 * @author Lay Li
 */
namespace lay\action;

use lay\core\Action;
use lay\entity\Response;
use lay\entity\Lister;
use lay\util\Util;
use lay\util\Logger;
use lay\util\Collector;

if(!defined('INIT_LAY')) {
    exit();
}

/**
 * 输出典型的JSON格式
 * @abstract
 * @author Lay Li
 */
abstract class TypicalAction extends JSONAction {
    /**
     * (non-PHPdoc)
     * @see \lay\core\JSONAction::onStop()
     */
    public function onStop() {
        $vars = $this->template->vars();
        $this->template->distinct();
        $this->template->push(Collector::response($this->name, $vars, true));
        parent::onStop();
    }
}
?>

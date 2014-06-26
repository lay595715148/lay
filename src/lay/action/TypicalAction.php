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
    protected $errorResponse = false;
    protected $errorMessage = '';
    protected $errorCode = 0;
    public function errorResponse($msg, $code = 0) {
        $this->errorResponse = true;
        $this->errorMessage = $msg;
        $this->errorCode = $code;
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\JSONAction::onStop()
     */
    public function onStop() {
        $vars = $this->template->vars();
        $this->template->distinct();
        if($this->errorResponse) {
            $this->template->push(Collector::errorResponse($this->name, $this->errorMessage, $this->errorCode));
        } else {
            $this->template->push(Collector::response($this->name, $vars, true));
        }
        parent::onStop();
    }
}
?>

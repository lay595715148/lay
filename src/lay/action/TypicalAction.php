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
    /**
     * 设置错误信息
     * @param string|array $msg 错误信息内容或数组，如：'invalid'或array(1000, 'invalid')
     * @param number $code 错误码，如$msg是数组则此参数无效
     */
    public function errorResponse($msg, $code = 1) {
        $this->errorResponse = true;
        if(is_string($msg)) {
            $this->errorMessage = $msg;
            $this->errorCode = $code;
        } else if(is_array($msg)) {
            $this->errorCode = $msg[0];
            $this->errorMessage = $msg[1];
        }
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
            $this->template->push(Collector::response($this->name, $vars));
        }
        parent::onStop();
    }
}
?>

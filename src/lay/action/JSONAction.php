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
 * 输出JSON格式
 * @abstract
 * @author Lay Li
 */
abstract class JSONAction extends Action {
    /**
     * (non-PHPdoc)
     * @see \lay\core\Action::onStop()
     */
    public function onStop() {
        parent::onStop();
        global $_START;
        $this->template->header('Content-Type: application/json');
        
        $headers = $this->template->headers();
        $vars = $this->template->vars();
        $res = Collector::response($this->name, $vars);
        //$res = Response::newInstance($vars, $this->name, true);
        
        foreach($headers as $header) {
            @header($header);
        }
        echo json_encode($res->toStdClass(), JSON_PRETTY_PRINT);
        //$this->template->json();
    }
}
?>

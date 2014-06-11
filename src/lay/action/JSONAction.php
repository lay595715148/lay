<?php
namespace lay\action;

use \lay\core\Action;
use lay\entity\Response;
use lay\util\Util;
use lay\entity\Lister;

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
        
        $headers = $this->template->headers();
        $vars = $this->template->vars();
        
        $res = Response::newInstance($vars, true, $this->name);

        foreach($headers as $header) {
            @header($header);
        }
        echo json_encode($res->toStdClass());
        //$this->template->json();
    }
}
?>

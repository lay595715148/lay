<?php
namespace plugin\http;

use lay\core\AbstractPlugin;
use lay\core\Action;
use lay\App;

class HttpPlugin extends AbstractPlugin {
    public function initilize() {
        $this->addHook(App::H_NONE_ACTION, array($this, 'noneAction'));
    }
    public function noneAction($app) {
        try {
            @header("HTTP/1.1 404 Not Found");
        } catch (Exception $e) {
            // has output
        }
        echo 404;
        exit();
    }
}
?>

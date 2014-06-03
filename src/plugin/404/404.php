<?php
use lay\core\AbstractPlugin;
use lay\core\Action;

class Http404Action extends AbstractPlugin {
    public function initilize() {
        $this->addHook(Action::HOOK_STOP, array($this, 'isFound'));
    }
    public function isFound($action) {
        if(!$action) {
            try {
                @header("HTTP/1.1 404 Not Found");
            } catch (Exception $e) {
                // has output
            }
            echo 404;
            exit();
        }
    }
}
?>

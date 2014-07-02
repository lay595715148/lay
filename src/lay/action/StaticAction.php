<?php
namespace lay\action;

use lay\core\EventEmitter;
use lay\core\Action;
use lay\util\Logger;

abstract class StaticAction extends HTMLAction {
    /**
     * return path of the archived file
     * @return string
     */
    public abstract function find();
    /**
     * make an archived file
     */
    public abstract function make();
    public function onRequest() {
        $static = $this->find();
        if(empty($static)) {
            parent::onRequest();
        }
    }
    public function onStop() {
        $static = $this->find();
        if(empty($static)) {
            $this->make();
        } else {
            $this->template->file($static);
        }
        parent::onStop();
    }
}
?>

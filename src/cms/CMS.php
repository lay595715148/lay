<?php
namespace cms;

use JSONAction;
use Template;
use Logger;

class CMS extends JSONAction {
    public function onGet() {
        
    }
    public function onPost() {
        
    }
    public function onRequest() {
        $this->template->push('$_SERVER', $_SERVER);
        Logger::debug('1');
    }
}
?>

<?php
namespace cms;

use lay\action\JSONAction;
use lay\core\emplate;
use lay\util\Logger;

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

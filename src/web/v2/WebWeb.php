<?php
namespace web\v2;

use lay\core\Action;
use lay\action\JSONAction;

class WebWeb extends JSONAction {
    public function onGet() {
        $this->template->push('test', 'test');
    }
}
?>

<?php
namespace plugin\oauth2;

use lay\action\JSONAction;

class Authorize extends JSONAction {
    public function onGet() {
        $this->template->push('action', 'Authorize');
    }
}
?>

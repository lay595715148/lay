<?php
class Authorize extends JSONAction {
    public function onGet() {
        $this->template->push('action', 'Authorize');
    }
}
?>
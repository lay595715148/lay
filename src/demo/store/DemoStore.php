<?php
class DemoStore extends Mysql {
    public function __construct() {
        parent::__construct(new DemoModel());
    }
}
?>

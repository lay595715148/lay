<?php
use lay\store\MysqlStore;

class DemoStore extends MysqlStore {
    public function __construct() {
        parent::__construct(new DemoModel());
    }
}
?>

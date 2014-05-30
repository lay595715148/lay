<?php
namespace demo\store;

use lay\store\MysqlStore;
use demo\model\DemoModel;

class DemoStore extends MysqlStore {
    public function __construct() {
        parent::__construct(new DemoModel());
    }
}
?>

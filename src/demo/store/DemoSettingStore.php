<?php
use lay\store\MysqlStore;

class DemoSettingStore extends MysqlStore {
    public function __construct() {
        parent::__construct(new DemoSetting());
    }
}
?>

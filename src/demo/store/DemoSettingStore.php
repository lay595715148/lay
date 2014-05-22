<?php
class DemoSettingStore extends MysqlStore {
    public function __construct() {
        parent::__construct(new DemoSetting());
    }
}
?>

<?php
namespace demo\store;

use lay\store\MysqlStore;
use demo\model\DemoSetting;

class DemoSettingStore extends MysqlStore {
    public function __construct() {
        parent::__construct(new DemoSetting());
    }
}
?>

<?php
class DemoSettingStore extends Mysql {
    public function __construct() {
        parent::__construct(new DemoSetting());
    }
}
?>

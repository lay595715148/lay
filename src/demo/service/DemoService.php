<?php
class DemoService extends Service {
    public function __construct() {
        parent::__construct(Store::getInstance('DemoStore'));
    }
    public function test() {
        Logger::debug($this->store->getModel()->schema());
    }
}
?>

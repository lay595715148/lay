<?php
class DemoService extends Service {
    /**
     * 
     * @var DemoStore
     */
    protected $store;
    public function __construct() {
        parent::__construct(Store::getInstance('DemoStore'));
    }
    public function test() {
        $dsStore = Store::getInstance('DemoSettingStore');
        Logger::debug($dsStore->count(array('k', 'att', 'like')));
        $ret = $this->store->select(array(), array(), array('id'=>'desc'), array());
        Logger::debug($ret);
    }
}
?>

<?php
class DemoService extends Service {
    /**
     * 
     * @var DemoStore
     */
    protected $store;
    /**
     * 
     * @var DemoUserMongo
     */
    private $demoUserMongo;
    public function __construct() {
        parent::__construct(Store::getInstance('DemoStore'));
    }
    public function select($info, $limit = array()) {
        return $this->store->select(array(), $info, array('id'=>'desc'), $limit);
    }
    public function test() {
        $this->demoUserMongo = Store::getInstance('DemoUserMongo');
        $this->demoUserMongo->connect();
        $ret = $this->demoUserMongo->get(2014);
        Logger::debug($ret);
        //$dsStore = Store::getInstance('DemoSettingStore');
        //Logger::debug($dsStore->count(array('k', 'att', 'like')));
        //$ret = $this->store->select(array(), array(), array('id'=>'desc'), array(0, 5));
        //Logger::debug($ret);
    }
}
?>

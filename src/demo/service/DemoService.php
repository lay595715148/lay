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
        //$this->demoUserMongo->connect();
        //$ret = $this->demoUserMongo->get(2014);
        $ret = $this->demoUserMongo->upd(2009, array('name' => 'demo'.rand(1, 100000)));
        Logger::debug($ret);
        $ret = $this->demoUserMongo->del(2008);
        Logger::debug($ret);
        //$ret = $this->demoUserMongo->add(array('name' => 'name'.rand(1, 10000), 'pass' => '060bade8c5f6306ee81c832bb469e067', 'nick' => 'lay'.rand(1, 1000)));
        //Logger::debug($ret);
        $ret = $this->demoUserMongo->select(array(), array('_id', 'name'), array(), array(5, 5));
        Logger::debug($ret);
        $ret = $this->demoUserMongo->count(array('_id' => array('$gt' => 2013)));
        Logger::debug($ret);
        //$dsStore = Store::getInstance('DemoSettingStore');
        //Logger::debug($dsStore->count(array('k', 'att', 'like')));
        //$ret = $this->store->select(array(), array(), array('id'=>'desc'), array(0, 5));
        //Logger::debug($ret);
    }
}
?>

<?php
namespace demo\service;

use lay\core\Service;
use lay\core\Store;
use lay\store\MemcacheStore;
use lay\util\Logger;
use demo\model\DemoUser;
use demo\store\DemoUserMongo;
use demo\store\DemoStore;
use web;

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
        parent::__construct(Store::getInstance('demo\store\DemoStore'));
    }
    public function select($info, $limit = array()) {
        return $this->store->select(array(), $info, array('id'=>'desc'), $limit);
    }
    public function test() {
        $a = new web\v2\Web();
        $b = new web\Web();
        $this->memcache();
    }
    public function demo() {
    }
    /**
     * 测试mongo
     */
    public function mongo() {
        $this->demoUserMongo = Store::getInstance('demo\store\DemoUserMongo');
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
    /**
     * 测试mecmache
     */
    public function memcache() {
        $this->demoUserMongo = Store::getInstance('demo\store\DemoUserMongo');
        $ret = $this->demoUserMongo->get(2014);
        $memcacheStore = new MemcacheStore(new DemoUser());
        $r = $memcacheStore->add($ret);
        $r = $memcacheStore->get(2014);
        Logger::debug($r, 'DEMO', true);
        $r = $memcacheStore->upd(2013, $ret);
        //$ret = $memcacheStore->del(2013);
        $ret = $memcacheStore->get(2013);
        Logger::debug($ret, 'DEMO', true);
    }
}
?>

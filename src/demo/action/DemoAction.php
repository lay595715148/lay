<?php
class DemoAction extends JsonAction {
    public function onCreate() {
        EventEmitter::on(Action::EVENT_GET, array(
            $this,
            'onGet2'
        ), 2);
    }
    public function onGet2() {
        $ret = $this->service('DemoService')->add(array('name' => 'demo'.rand(1, 100000), 'datetime' => date('Y-m-d H:i:s')));
        Logger::debug($ret);
        //$ret = $this->service('DemoService')->count(array('type' => '0'));
        //Logger::debug($ret);
    }
    public function onGet() {
        $ret = $this->service('DemoService')->del(50);
        Logger::debug($ret);
        $ret = $this->service('DemoService')->get(32);
        Logger::debug($ret);
        $ret = $this->service('DemoService')->upd(32, array('name' => $_GET['name'].rand(1, 100000)));
        Logger::debug($ret);
        $ret = $this->service('DemoService')->get(32);
        Logger::debug($ret);
        $ret = $this->service('DemoService')->count(array('type' => '1'));
        Logger::debug($ret);
    }
}
?>

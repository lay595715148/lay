<?php
class DemoAction extends JsonAction {
    /**
     * 
     * @var DemoService
     */
    private $demoService;
    public function onCreate() {
        EventEmitter::on(Action::EVENT_GET, array(
            $this,
            'onGet2'
        ), 2);
        $this->demoService = $this->service('DemoService');
    }
    public function onGet2() {
        $ret = $this->service('DemoService')->add(array('name' => 'demo'.rand(1, 100000), 'datetime' => date('Y-m-d H:i:s')));
        Logger::debug($ret);
        //$ret = $this->service('DemoService')->count(array('type' => '0'));
        //Logger::debug($ret);
    }
    public function onGet() {
        $ret = $this->demoService->test();
        //$this->demoService->test()
        $ret = $this->demoService->del(50);
        Logger::debug($ret);
        $ret = $this->demoService->get(32);
        Logger::debug($ret);
        $ret = $this->demoService->upd(32, array('name' => $_GET['name'].rand(1, 100000)));
        Logger::debug($ret);
        $ret = $this->demoService->get(32);
        Logger::debug($ret);
        $ret = $this->demoService->count(array(array('type', '1,2', 'in', 'OR')));
        Logger::debug($ret);
    }
}
?>

<?php
use lay\core\Action;
use lay\action\JSONAction;
use lay\core\Coder;
use lay\core\EventEmitter;
use lay\util\Logger;

class DemoAction extends JSONAction {
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
        //$ret = $this->demoService->test();
        //$ret = $this->demoService->add(array('name' => 'demo'.rand(1, 100000), 'datetime' => date('Y-m-d H:i:s')));
        //Logger::debug($ret);
        //$ret = $this->demoService->count(array('type' => '0'));
        //Logger::debug($ret);
        //$ret = $this->demoService->select(array('type' => '0'), array(0, 2));
        //Logger::debug($ret);
        $a = array('$f','$d', '_id' => array('$gt' => 2010));
        $ret = Coder::array2Code($a);
        Logger::debug($ret);
        $ret = json_encode($a);
        Logger::debug($ret);
    }
    public function onGet() {
        $ret = $this->demoService->test();
        //$this->test();
    }
    public function test() {
        $ret = $this->demoService->del(50);
        Logger::debug($ret);
        $ret = $this->demoService->get(32);
        Logger::debug($ret);
        $ret = $this->demoService->upd(32, array('name' => $_GET['name'].rand(1, 100000)));
        Logger::debug($ret);
        $ret = $this->demoService->count(array(array('type', '1,2', 'in', 'OR', array('table' => true))));
        Logger::debug($ret);
    }
}
?>

<?php
namespace web\v2;

use lay\core\Action;
use lay\action\JSONAction;
use demo\model\DemoModel;
use lay\core\Service;
use demo\service\DemoService;

class WebWeb extends JSONAction {
    /**
     * 
     * @var DemoService
     */
    public $service;
    public function onGet() {
        $this->service = Service::getInstance('demo\service\DemoService');
        $ret = $this->service->demo();
        $this->template->push($ret);
    }
}
?>

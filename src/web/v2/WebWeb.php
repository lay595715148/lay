<?php
namespace web\v2;

use lay\core\Action;
use lay\action\JSONAction;
use demo\model\DemoModel;
use lay\core\Service;
use demo\service\DemoService;
use lay\action\TypicalAction;

class WebWeb extends TypicalAction {
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

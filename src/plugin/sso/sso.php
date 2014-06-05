<?php
use lay\App;
use lay\core\Action;
use lay\core\AbstractPlugin;
use lay\util\Logger;

class SSO extends AbstractPlugin {
    public function initilize() {
        $this->addHook(App::H_INIT, array($this, 'sayhello'));
        $this->addHook(Action::H_CREATE, array($this, 'sayhello2'));
        $this->addHook(Action::H_STOP, array($this, 'sayhello3'));
    }
    public function sayhello() {
        //App::s
        //Web::test();echo '<br>';
        Logger::debug('SSO say hello!');
    }
    public function sayhello2($action) {
        Logger::debug('SSO say hello2!');
    }
    public function sayhello3() {
        //Web::test();echo '<br>';
        Logger::debug('SSO say hello3!');
    }
}
?>

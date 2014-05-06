<?php
class Oauth2Plugin extends Plugin {
    public function initilize() {
        $this->addHook(Lay::HOOK_INIT, array($this, 'sayhello'));
        $this->addHook(Action::HOOK_CREATE, array($this, 'sayhello2'));
        $this->addHook(Action::HOOK_DESTROY, array($this, 'sayhello3'));
    }
    public function sayhello() {
        //Lay::s
        //Web::test();echo '<br>';
        Logger::debug('say hello!');
    }
    public function sayhello2($action) {
        Logger::debug('say hello2!');
    }
    public function sayhello3($action) {
        //Web::test();echo '<br>';
        Logger::debug('say hello3!');
    }
}
?>

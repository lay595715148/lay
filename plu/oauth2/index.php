<?php
class OAuth2Plugin extends Plugin {
    public function initilize() {
        $this->addHook('lay_initilize', array($this, 'sayhello'));
        $this->addHook('action_create', array($this, 'sayhello2'));
        $this->addHook('action_destroy', array($this, 'sayhello3'));
    }
    public function sayhello() {
        Web::test();echo '<br>';
        Logger::debug('say hello!');
    }
    public function sayhello2() {
        Web::test();echo '<br>';
        Logger::debug('say hello2!');
    }
    public function sayhello3() {
        //Web::test();echo '<br>';
        Logger::debug('say hello3!');
    }
}
?>

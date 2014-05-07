<?php
class Oauth2Plugin extends Plugin {
    private $action;
    public function initilize() {
        $this->addHook(Lay::HOOK_INIT, array($this, 'sayhello'));
        $this->addHook(Action::HOOK_CREATE, array($this, 'sayhello2'));
        $this->addHook(Action::HOOK_DESTROY, array($this, 'sayhello3'));
        $this->addHook(Action::HOOK_STOP, array($this, 'sayhello4'));
    }
    public function sayhello() {
        //Lay::s
        //Web::test();echo '<br>';
        Logger::debug('say hello!');
    }
    /**
     * 
     * @param Action $action
     */
    public function sayhello2($action) {
        $this->action = $action;
        Logger::debug('say hello2!');
    }
    public function sayhello3($action) {
        $this->action = $action;
        //Web::test();echo '<br>';
        Logger::debug('say hello3!');
    }
    public function sayhello4($action) {
        $this->action->getTemplate()->push('emitted', EventEmitter::emittedEvents());
        $this->action->getTemplate()->push('actived', PluginManager::activedHooks());
        //Web::test();echo '<br>';
        Logger::debug('say hello3!');
    }
}
?>

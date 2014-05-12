<?php
class Oauth2Plugin extends AbstractPlugin {
    private $action;
    public function initilize() {
        $this->addHook(App::HOOK_INIT, array($this, 'sayhello'));
        $this->addHook(Action::HOOK_CREATE, array($this, 'sayhello2'));
        $this->addHook(Action::HOOK_STOP, array($this, 'sayhello3'));
    }
    public function sayhello() {
        //App::s
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
    public function sayhello3() {
        $this->action->getTemplate()->push('emitted', EventEmitter::emittedEvents());
        $this->action->getTemplate()->push('actived', PluginManager::activedHooks());
        Logger::debug('say hello3!');
    }
}
?>

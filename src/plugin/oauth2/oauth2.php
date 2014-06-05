<?php
use lay\App;
use lay\core\Action;
use lay\core\EventEmitter;
use lay\core\PluginManager;
use lay\core\AbstractPlugin;
use lay\util\Logger;

class Oauth2Plugin extends AbstractPlugin {
    private $action;
    public function initilize() {
        $this->addHook(App::H_INIT, array($this, 'register'));
        $this->addHook(Action::H_CREATE, array($this, 'sayhello2'));
        $this->addHook(Action::H_STOP, array($this, 'sayhello3'));
    }
    /**
     * 
     * @param App $app
     */
    public function register($app) {
        $app->addClasspath(realpath(__DIR__.'/classes'));
        $app->configure(realpath(__DIR__.'/config.inc.php'));
    }
    /**
     * 
     * @param Action $action
     */
    public function sayhello2($action) {
        $this->action = $action;
        //Logger::debug('say hello2!');
    }
    public function sayhello3() {
        $this->action->getTemplate()->push('emitted', EventEmitter::emittedEvents());
        $this->action->getTemplate()->push('actived', PluginManager::activedHooks());
        //Logger::debug('say hello3!');
    }
}
?>

<?php
class Index extends JSONAction {
    public function onCreate() {
        Logger::debug('Index onCreate');
    }
    public function onGet() {
        $ismatch = preg_match_all('/^\/u\/(?P<id>[0-9]+)$/', '/u/8282', $matches, PREG_SET_ORDER);
        //var_dump($ismatch);Logger::debug($matches);
        Logger::debug('Index onGet');
    }
    public function onPost() {
        Logger::debug('Index onPost');
    }
    public function onRequest() {
        $params = $this->scope->param();
        $this->template->push($params);
        Logger::debug('Index onRequest');
    }
    public function onStop() {
        parent::onStop();
        //Logger::debug('Index onStop');
    }
    public function onDestroy() {
        Logger::debug(EventEmitter::emittedEvents());
        Logger::debug(PluginManager::activedHooks());
        Logger::debug('Index onDestroy');
    }
}
?>

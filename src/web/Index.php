<?php
class Index extends Action {
    public function __construct($name = 'index') {
        parent::__construct($name, new Template());
    }
    public function onCreate() {
        Logger::debug('Index onCreate');
    }
    public function onRun() {
        Logger::debug('Index onRun');
    }
    public function onGet() {
        Logger::debug('Index onGet');
    }
    public function onPost() {
        Logger::debug('Index onPost');
    }
    public function onRequest() {
        Logger::debug('Index onRequest');
    }
    public function onStop() {
        Logger::debug('Index onStop');
    }
    public function onDestroy() {
        Logger::debug('Index onDestroy');
    }
}
?>

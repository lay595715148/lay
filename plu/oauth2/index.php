<?php
class OAuth2Plugin extends Plugin {
    public function initilize() {
        $this->addHook('lay_initilize', array($this, 'sayhello'));
    }
    public function sayhello() {
        $cms = new cms\CMS();
        $cms->onCreate();echo '<br>';Web::test();echo '<br>';V2_Web::test();
        Logger::debug('say hello!');
    }
}
?>

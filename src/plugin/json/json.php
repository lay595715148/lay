<?php
use lay\core\AbstractPlugin;
use lay\core\Action;
use lay\App;

class JsonPlugin extends AbstractPlugin {
    public static $json;
    public function initilize() {
        $this->addHook(App::H_INIT, array($this, 'genCmdAction'));
    }
    /**
     * 
     * @param App $app
     */
    public function genCmdAction($app) {
        if($_REQUEST['json']) {
            $json = $_REQUEST['json'];
            $json = rawurldecode($json);
            $json = json_decode($json, true);
            if(empty($json) || empty($json['cmd'])) {
                //return error
                return;
            }
            JsonPlugin::$json = $json;
            
            $cmd = $json['cmd'];
            $nsArr = explode('.', $cmd);
            $cnArr = explode('-', array_pop($nsArr));
            $ns = implode('\\', $nsArr);
            $cn = implode('', array_map('ucfirst', $cnArr));
            $classname = ($ns ? $ns . '\\' : '') . $cn;
            $app->setAction(Action::getInstance($cmd, $classname));
        }
    }
}
?>

<?php
namespace lay\action;

use lay\core\EventEmitter;
use lay\action\StaticAction;
use lay\util\Logger;
use lay\App;
use lay\util\Util;

abstract class TypicalStaticAction extends StaticAction {
    /**
     * the found file
     * @var string
     */
    protected $found;
    /**
     * return static dir name by root path
     * @return string
     */
    public abstract function dir();
    /**
     * if can make
     * @return boolean
     */
    public abstract function can();
    public function find() {
        if(empty($this->found)) {
            $id = intval(App::$_Parameter['id']);
            $template = $this->template->getFile();
            $dir = App::$_RootPath . DIRECTORY_SEPARATOR . $this->dir() . DIRECTORY_SEPARATOR;
            $filename = realpath($dir . $id . '.html');
            if(is_file($filename) && is_file($template)) {
                $origin = filemtime($template);
                $static = filemtime($filename);
                if($static > $origin) {
                    $this->found = $filename;
                } else {
                    $this->found = false;
                }
            } else {
                $this->found = false;
            }
        }
        return $this->found;
    }
    public function make() {
        if ($this->can()) {
            $id = intval(App::$_Parameter['id']);
            $dir = App::$_RootPath . DIRECTORY_SEPARATOR . $this->dir() . DIRECTORY_SEPARATOR;
            $filename = $dir . $id . '.html';
            $content = $this->template->out();
            $mkdir = is_dir($dir) ? true : Util::createFolders($dir);
            $handle = fopen($filename, 'w');
            $result = fwrite($handle, $content);
            $return = fflush($handle);
            $return = fclose($handle);
        } else {
            $result = false;
        }
        return $result;
    }
}
?>

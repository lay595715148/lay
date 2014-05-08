<?php
if(! defined('INIT_LAY')) {
    exit();
}

class Collector {
    public static function lister($list, $total, $hasNext) {
        $lister = new Lister();
        $lister->list = $list;
        $lister->total = $total;
        $lister->hasNext = $hasNext;
        return $lister;
    }
    public static function response($success, $action, $content, $code) {
        $response = new Response();
        $response->success = $success;
        $response->action = $action;
        $response->content = $content;
        $response->code = $code;
        return $response;
    }
}
?>

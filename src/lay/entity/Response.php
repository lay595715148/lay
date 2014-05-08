<?php
if(! defined('INIT_LAY')) {
    exit();
}

class Response extends Entity {
    public function __construct() {
        parent::__construct(array(
            'success' => false,
            'action' => '',
            'content' => '',
            'code' => 0
        ), array(
            'success' => Bean::PROPETYPE_BOOLEAN,
            'action' => Bean::PROPETYPE_STRING,
            'code' => Bean::PROPETYPE_INTEGER
        ));
    }
    public function summary() {
        return array(
            'success' => 'success',
            'action' => 'action',
            'content' => 'content',
            'code' => 'code'
        );
    }
    public function toSummary() {
        return $this->toArray();
    }
}
?>

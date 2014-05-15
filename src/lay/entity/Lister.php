<?php
if(! defined('INIT_LAY')) {
    exit();
}

class Lister extends Entity {
    public function __construct() {
        parent::__construct(array(
            'list' => array(),
            'total' => 0,
            'hasNext' => false
        ));
    }
    protected function rules() {
        return  array(
            'list' => Bean::PROPETYPE_ARRAY,
            'total' => Bean::PROPETYPE_INTEGER,
            'hasNext' => Bean::PROPETYPE_BOOLEAN
        );
    }
    public function summary() {
        return array(
            'list' => 'list',
            'total' => 'total',
            'hasNext' => 'hasNext'
        );
    }
    public function toSummary() {
        return $this->toArray();
    }
}
?>

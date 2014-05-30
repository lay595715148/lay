<?php
namespace demo\model;

use lay\core\Model;
use lay\core\Bean;

class DemoModel extends Model {
    public function __construct() {
        parent::__construct(array(
                'id' => 0,
                'name' => '',
                'datetime' => '0000-00-00 00:00:00',
                'type' => 0
        ));
    }
    protected function rules() {
        return array(
                'id' => Bean::PROPETYPE_INTEGER,
                'name' => Bean::PROPETYPE_STRING,
                'datetime' => Bean::PROPETYPE_DATETIME,
                'type' => Bean::PROPETYPE_INTEGER
        );
    }
    public function schema() {
        return 'laysoft';
    }
    public function table() {
        return 'lay_demo';
    }
    /**
     * return mapping between object property and table fields
     * @return array
     */
    public function columns() {
        return array(
                'id' => 'id',
                'name' => 'name',
                'datetime' => 'datetime',
                'type' => 'type'
        );
    }
    /**
     * return table priamry key
     * @return array
     */
    public function primary() {
        return 'id';
    }
}
?>

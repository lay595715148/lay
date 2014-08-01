<?php
namespace demo\model;

use lay\core\Model;
use lay\core\Bean;
use lay\util\Logger;

class DemoModel extends Model {
    //private $id = 0;
    //private $name = '';
    //private $datetime = '0000-00-00 00:00:00';
    //private $type = 0;
    public function __construct() {
    }
    /**
     * @return array
     */
    public function properties() {
        return array(
                'id' => 0,
                'name' => '',
                'datetime' => '0000-00-00 00:00:00',
                'type' => 0
        );
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

<?php
namespace demo\model;

use lay\core\Model;
use lay\core\Bean;

class DemoSetting extends Model {
    public function __construct() {
        parent::__construct(array(
                'id' => 0,
                'k' => '',
                'v' => ''
        ));
    }
    protected function rules() {
        return array(
                'id' => Bean::PROPETYPE_INTEGER,
                'k' => Bean::PROPETYPE_STRING,
                'v' => Bean::PROPETYPE_STRING
        );
    }
    public function table() {
        return 'lay_cfg_setting';
    }
    /**
     * return mapping between object property and table fields
     * @return array
     */
    public function columns() {
        return array(
                'id' => 'id',
                'k' => 'k',
                'v' => 'v'
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

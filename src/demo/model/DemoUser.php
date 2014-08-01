<?php
namespace demo\model;

use lay\core\Model;
use lay\core\Bean;
use lay\model\ExpireIncrementer;

class DemoUser extends ExpireIncrementer {
    //private $id = 0;
    //private $name = '';
    //private $nick = '';
    //private $pass = '';
    //private $lifetime = 1800;
    public function __construct() {
    }
    /**
     * @return array
     */
    public function properties() {
        return array(
                'id' => 0,
                'name' => '',
                'nick' => '',
                'pass' => ''
        );
    }
    public function getLifetime() {
        return $this->lifetime;
    }
    public function setLifetime($lifetime) {
        $this->lifetime = intval($lifetime);
    }
    protected function rules() {
        return array(
                'id' => Bean::PROPETYPE_INTEGER,
                'name' => Bean::PROPETYPE_STRING,
                'nick' => Bean::PROPETYPE_STRING,
                'pass' => Bean::PROPETYPE_STRING
        );
    }
    public function schema() {
        return 'laysoft';
    }
    public function table() {
        return 'lay_user';
    }
    /**
     * return mapping between object property and table fields
     * @return array
     */
    public function columns() {
        return array(
                'id' => '_id',
                'name' => 'name',
                'nick' => 'nick',
                'pass' => 'pass'
        );
    }
    /**
     * return table priamry key
     * @return array
     */
    public function primary() {
        return '_id';
    }
    public function sequence() {
        return '_id';
    }
}
?>

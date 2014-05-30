<?php
use lay\core\Model;
use lay\core\I_Increment;
use lay\model\ModelExpire;

class DemoUser extends ModelExpire implements I_Increment {
    public function __construct() {
        parent::__construct(array(
                'id' => 0,
                'name' => '',
                'nick' => '',
                'pass' => ''
        ));
    }
    private $lifetime = 1800;
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

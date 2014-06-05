<?php
namespace lay\model;

use lay\core\Model;

/**
 * 针对mongodb的主键自增涨模型
 * @author Lay Li
 */
class MongoSequence extends Model {
    public function __construct() {
        parent::__construct(array(
                'id' => '',
                'name' => '',
                'seq' => 0
        ));
    }
    protected function rules() {
        return array(
                'name' => Bean::PROPETYPE_STRING,
                'seq' => Bean::PROPETYPE_INTEGER
        );
    }
    public function schema() {
        return 'laysoft';
    }
    public function table() {
        return 'lay_sequence';
    }
    /**
     * return mapping between object property and table fields
     * @return array
     */
    public function columns() {
        return array(
                'id' => '_id',
                'name' => 'name',
                'seq' => 'seq'
        );
    }
    /**
     * return table priamry
     * @return array
     */
    public function primary() {
        return 'name';
    }
}
?>

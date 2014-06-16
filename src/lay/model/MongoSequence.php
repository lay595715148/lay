<?php
/**
 * 针对mongodb的主键自增涨模型
 * @author Lay Li
 */
namespace lay\model;

use lay\core\Model;
use lay\core\Bean;

/**
 * 针对mongodb的主键自增涨模型
 * @author Lay Li
 */
class MongoSequence extends Model {
    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct(array(
                'id' => '',
                'name' => '',
                'seq' => 0
        ));
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Bean::rules()
     */
    protected function rules() {
        return array(
                'name' => Bean::PROPETYPE_STRING,
                'seq' => Bean::PROPETYPE_INTEGER
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Model::schema()
     */
    public function schema() {
        return 'laysoft';
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Model::table()
     */
    public function table() {
        return 'lay_sequence';
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Model::columns()
     */
    public function columns() {
        return array(
                'id' => '_id',
                'name' => 'name',
                'seq' => 'seq'
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Model::primary()
     */
    public function primary() {
        return 'name';
    }
}
?>

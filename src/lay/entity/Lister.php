<?php
namespace lay\entity;

use \lay\core\Entity;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 结构化列表数据对象
 * @author Lay Li
 * @property array $list
 * @property int $total
 * @property boolean $hasNext
 * @method void setList(array $list) 给list属性赋值
 * @method void setTotal(int $total) 给total属性赋值
 * @method void setHasNext(boolean $hasNext) 给hasNext属性赋值
 * @method array getList() 获取list属性值
 * @method int getTotal() 获取total属性值
 * @method boolean getHasNext() 获取hasNext属性值
 */
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

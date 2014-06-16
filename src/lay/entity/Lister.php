<?php
/**
 * 结构化列表数据对象
 * @author Lay Li
 */
namespace lay\entity;

use lay\core\Entity;
use lay\core\Bean;
use lay\util\Collector;

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
    const PROPETYPE_LISTER = 'lister';
    /**
     * 创建Lister新实例
     * @param array $list
     * @param string $total
     * @param string $hasNext
     * @return Lister
     */
    public static function newInstance($list, $total = false, $hasNext = false) {
        return Collector::lister($list, $total, $hasNext ? true : false);
    }
    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct(array(
            'list' => array(),
            'total' => 0,
            'hasNext' => false
        ));
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Bean::rules()
     */
    protected function rules() {
        return  array(
            'list' => Bean::PROPETYPE_PURE_ARRAY,
            'total' => Bean::PROPETYPE_INTEGER,
            'hasNext' => Bean::PROPETYPE_BOOLEAN
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Entity::summary()
     */
    public function summary() {
        return array(
            'list' => 'list',
            'total' => 'total',
            'hasNext' => 'hasNext'
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Entity::toSummary()
     */
    public function toSummary() {
        return $this->toArray();
    }
}
?>

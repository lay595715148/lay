<?php
/**
 * 结构化响应返回数据对象
 * @author Lay Li
 */
namespace lay\entity;

use lay\core\Entity;
use lay\core\Bean;
use lay\util\Util;
use lay\util\Collector;

if(! defined('INIT_LAY')) {
    exit();
}

/**
 * 结构化响应返回数据对象
 * @author Lay Li
 * @property boolean $success
 * @property string $action
 * @property mixed $content
 * @property int $code
 * @property number $exp
 */
class Response extends Entity {
    const PROPETYPE_RESPONSE = 'response';
    /**
     * 创建自身的一个新实例
     * @param mixed $content
     * @param string $success
     * @param string $action
     * @param number $code
     * @return Response
     */
    public static function newInstance($content, $action = '', $success = true, $code = 0) {
        return Collector::response($action, $content, $success, $code);
    }
    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct(array(
            'success' => false,
            'action' => '',
            'content' => '',
            'code' => 0,
            'exp' => 0
        ));
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Bean::rules()
     */
    protected function rules() {
        return  array(
            'success' => Bean::PROPETYPE_BOOLEAN,
            'action' => Bean::PROPETYPE_STRING,
            'content' => array(Bean::PROPETYPE_S_OTHER => Lister::PROPETYPE_LISTER),
            'code' => Bean::PROPETYPE_INTEGER,
            'exp' => Bean::PROPETYPE_NUMBER
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Entity::summary()
     */
    public function summary() {
        return array(
            'success' => 'success',
            'action' => 'action',
            'content' => 'content',
            'code' => 'code',
            'expend' => 'expend'
        );
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Entity::toSummary()
     */
    public function toSummary() {
        return $this->toArray();
    }
    /**
     * (non-PHPdoc)
     * @see \lay\core\Bean::otherFormat()
     */
    /**
     * (non-PHPdoc)
     * @see \lay\core\Bean::otherFormat()
     * @param mixed $value 值
     * @param mixed $propertype 配置类型
     * @return mixed
     */
    protected function otherFormat($value, $propertype) {
        //格式化content中的纯数组内容，
        if($propertype == Lister::PROPETYPE_LISTER) {
            if(Util::isPureArray($value)) {
                return Lister::newInstance($value, count($value));
            }
        }
        return $value;
    }
}
?>

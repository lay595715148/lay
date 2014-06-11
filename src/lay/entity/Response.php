<?php
namespace lay\entity;

use lay\core\Entity;
use lay\core\Bean;
use lay\util\Util;

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
        $instance = new Response();
        $instance->content = $content;
        $instance->success = $success;
        $instance->action = $action;
        $instance->code = $code;
        return $instance;
    }
    public function __construct() {
        parent::__construct(array(
            'success' => false,
            'action' => '',
            'content' => '',
            'code' => 0
        ));
    }
    protected function rules() {
        return  array(
            'success' => Bean::PROPETYPE_BOOLEAN,
            'action' => Bean::PROPETYPE_STRING,
            'content' => array(Bean::PROPETYPE_S_OTHER => Lister::PROPETYPE_LISTER),
            'code' => Bean::PROPETYPE_INTEGER
        );
    }
    public function summary() {
        return array(
            'success' => 'success',
            'action' => 'action',
            'content' => 'content',
            'code' => 'code'
        );
    }
    public function toSummary() {
        return $this->toArray();
    }
    /**
     * 格式化content中的纯数组内容，
     */
    protected function otherFormat($value, $propertype) {
        if($propertype == Lister::PROPETYPE_LISTER) {
            if(Util::isPureArray($value)) {
                return Lister::newInstance($value, count($value));
            }
        }
        return $value;
    }
}
?>
